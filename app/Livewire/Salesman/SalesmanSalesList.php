<?php

namespace App\Livewire\Salesman;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use App\Models\ReturnsProduct;
use App\Models\ProductStock;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

#[Title('My Sales')]
#[Layout('components.layouts.salesman')]
class SalesmanSalesList extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $deliveryFilter = '';
    public $selectedSale = null;
    public $showDetailsModal = false;

    // Edit sale properties
    public $showEditModal = false;
    public $editingSale = null;
    public $editItems = [];
    public $editNotes = '';
    public $editDiscount = 0;
    public $editDiscountType = 'fixed'; // 'fixed' or 'percentage'

    // Return properties
    public $showReturnModal = false;
    public $returnItems = [];
    public $returnNotes = '';
    public $saleReturns = [];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedDeliveryFilter()
    {
        $this->resetPage();
    }

    public function viewDetails($saleId)
    {
        $this->selectedSale = Sale::with(['customer', 'items.product', 'payments', 'approvedBy', 'deliveredBy', 'returns.product'])
            ->find($saleId);

        // Load returns for this sale
        $this->saleReturns = ReturnsProduct::where('sale_id', $saleId)
            ->with('product')
            ->get();

        $this->showDetailsModal = true;
    }

    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->selectedSale = null;
        $this->saleReturns = [];
    }

    // Return Methods
    public function openReturnModal($saleId)
    {
        $sale = Sale::with(['items.product', 'customer'])->find($saleId);

        if (!$sale) {
            $this->showToast('error', 'Sale not found.');
            return;
        }

        // Only allow returns on approved/confirmed sales
        if ($sale->status !== 'confirm') {
            $this->showToast('error', 'Returns can only be made on approved sales.');
            return;
        }

        $this->selectedSale = $sale;
        $this->returnNotes = '';

        // Prepare return items
        $this->returnItems = [];
        foreach ($sale->items as $item) {
            // Calculate already returned quantity
            $returnedQty = ReturnsProduct::where('sale_id', $saleId)
                ->where('product_id', $item->product_id)
                ->sum('return_quantity');

            $availableQty = $item->quantity - $returnedQty;

            $this->returnItems[] = [
                'item_id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'variant_id' => $item->variant_id ?? null,
                'variant_value' => $item->variant_value ?? null,
                'original_qty' => $item->quantity,
                'returned_qty' => $returnedQty,
                'available_qty' => $availableQty,
                'return_qty' => 0,
                'unit_price' => $item->unit_price,
            ];
        }

        $this->showReturnModal = true;
    }

    public function closeReturnModal()
    {
        $this->showReturnModal = false;
        $this->returnItems = [];
        $this->returnNotes = '';
    }

    public function updateReturnQty($index, $qty)
    {
        if (isset($this->returnItems[$index])) {
            $maxQty = $this->returnItems[$index]['available_qty'];
            $this->returnItems[$index]['return_qty'] = max(0, min((int)$qty, $maxQty));
        }
    }

    public function getReturnTotalProperty()
    {
        $total = 0;
        foreach ($this->returnItems as $item) {
            $total += $item['return_qty'] * $item['unit_price'];
        }
        return $total;
    }

    public function processReturn()
    {
        if (!$this->selectedSale) {
            return;
        }

        // Check if any items are being returned
        $hasReturns = false;
        foreach ($this->returnItems as $item) {
            if ($item['return_qty'] > 0) {
                $hasReturns = true;
                break;
            }
        }

        if (!$hasReturns) {
            $this->showToast('error', 'Please select at least one item to return.');
            return;
        }

        try {
            DB::beginTransaction();

            $totalReturnAmount = 0;

            foreach ($this->returnItems as $item) {
                if ($item['return_qty'] > 0) {
                    $returnAmount = $item['return_qty'] * $item['unit_price'];
                    $totalReturnAmount += $returnAmount;

                    // Create return record
                    ReturnsProduct::create([
                        'sale_id' => $this->selectedSale->id,
                        'product_id' => $item['product_id'],
                        'variant_id' => $item['variant_id'] ?? null,
                        'variant_value' => $item['variant_value'] ?? null,
                        'return_quantity' => $item['return_qty'],
                        'selling_price' => $item['unit_price'],
                        'total_amount' => $returnAmount,
                        'notes' => $this->returnNotes,
                        'return_type' => 'customer',
                        'user_id' => Auth::id(),
                    ]);

                    // Update stock (add back returned items) using FIFOStockService
                    try {
                        \App\Services\FIFOStockService::restoreStock(
                            $item['product_id'], 
                            $item['return_qty'], 
                            $item['variant_id'] ?? null, 
                            $item['variant_value'] ?? null
                        );
                    } catch (\Exception $e) {
                        Log::error('Stock restoration failed for return: ' . $e->getMessage());
                    }
                }
            }

            // ✅ Correct calculation: Recalculate with discount percentage
            // Step 1: Get current subtotal from all sale items
            $currentSubtotal = SaleItem::where('sale_id', $this->selectedSale->id)
                ->get()
                ->sum(function ($item) {
                    return ($item->unit_price * $item->quantity) - ($item->discount_per_unit * $item->quantity);
                });

            // Step 2: Subtract return amount from subtotal
            $newSubtotal = $currentSubtotal - $totalReturnAmount;

            // Step 3: Recalculate discount based on sale's additional discount settings
            $discountAmount = 0;
            if ($this->selectedSale->additional_discount_type === 'percentage' && $this->selectedSale->additional_discount_percentage > 0) {
                $discountAmount = ($newSubtotal * $this->selectedSale->additional_discount_percentage) / 100;
            } elseif ($this->selectedSale->additional_discount_type === 'fixed') {
                // For fixed discount, keep it as is (but don't exceed new subtotal)
                $discountAmount = min($this->selectedSale->discount_amount ?? 0, $newSubtotal);
            }

            // Step 4: Calculate new total
            $newTotal = $newSubtotal - $discountAmount;

            // Step 5: Update due amount proportionally
            $previousTotal = $this->selectedSale->total_amount;
            $totalReduction = $previousTotal - $newTotal;
            $newDue = max(0, $this->selectedSale->due_amount - $totalReduction);

            $this->selectedSale->update([
                'subtotal' => $newSubtotal,
                'discount_amount' => $discountAmount,
                'total_amount' => $newTotal,
                'due_amount' => $newDue,
            ]);

            DB::commit();

            $this->closeReturnModal();
            $this->showToast('success', 'Return processed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Return processing error: ' . $e->getMessage());
            $this->showToast('error', 'Error processing return.');
        }
    }

    public function render()
    {
        $query = Sale::where('user_id', Auth::id())
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('sale_id', 'like', '%' . $this->search . '%')
                        ->orWhere('invoice_number', 'like', '%' . $this->search . '%')
                        ->orWhereHas('customer', function ($cq) {
                            $cq->where('name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->statusFilter, function ($q) {
                $q->where('status', $this->statusFilter);
            })
            ->when($this->deliveryFilter, function ($q) {
                $q->where('delivery_status', $this->deliveryFilter);
            })
            ->with(['customer'])
            ->orderBy('created_at', 'desc');

        return view('livewire.salesman.salesman-sales-list', [
            'sales' => $query->paginate(15),
            'pendingCount' => Sale::where('user_id', Auth::id())->where('status', 'pending')->count(),
            'approvedCount' => Sale::where('user_id', Auth::id())->where('status', 'confirm')->count(),
            'rejectedCount' => Sale::where('user_id', Auth::id())->where('status', 'rejected')->count(),
        ]);
    }

    /**
     * Show toast notification with custom styling
     * 
     * @param string $type - 'success', 'error', 'warning', 'info'
     * @param string $message - The message to display
     */
    private function showToast($type, $message)
    {
        $bgColors = [
            'success' => '#10b981',
            'error' => '#ef4444',
            'warning' => '#f59e0b',
            'info' => '#3b82f6',
        ];

        $icons = [
            'success' => '✓',
            'error' => '✕',
            'warning' => '⚠',
            'info' => 'ℹ',
        ];

        $bg = $bgColors[$type] ?? $bgColors['info'];
        $icon = $icons[$type] ?? $icons['info'];

        $escapedMessage = addslashes($message);

        $this->js("
            const toast = document.createElement('div');
            toast.style.cssText = 'position:fixed;top:20px;right:20px;background:{$bg};color:white;padding:16px 24px;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.15);z-index:9999;font-size:14px;font-weight:600;display:flex;align-items:center;gap:12px;animation:slideIn 0.3s ease;min-width:300px;max-width:500px;';
            toast.innerHTML = '<span style=\"font-size:20px;font-weight:bold;\">{$icon}</span><span>{$escapedMessage}</span>';
            document.body.appendChild(toast);
            
            const style = document.createElement('style');
            style.textContent = '@keyframes slideIn { from { transform: translateX(400px); opacity: 0; } to { transform: translateX(0); opacity: 1; } } @keyframes slideOut { from { transform: translateX(0); opacity: 1; } to { transform: translateX(400px); opacity: 0; } }';
            document.head.appendChild(style);
            
            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        ");
    }
}
