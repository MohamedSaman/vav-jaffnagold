<?php

namespace App\Livewire\ShopStaff;

use App\Models\ReturnsProduct;
use App\Models\Sale;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('My Sales')]
#[Layout('components.layouts.shop-staff')]
class ShopStaffSalesList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';

    public $statusFilter = '';

    public $dateFilter = '';

    public $paymentTypeFilter = '';

    public function mount()
    {
        $this->dateFilter = now()->format('Y-m-d');
    }

    // View details
    public $selectedSale = null;

    public $showDetailsModal = false;

    public $saleReturns = [];

    // Dummy Bill Generator state
    public $showDummyModal = false;

    public $dummySaleId = null;

    public $dummyInvoiceNumber = '';

    public $dummyCustomerName = '';

    public $dummyOriginalTotal = 0;

    public $dummyTargetTotal = '';

    public $dummyItems = [];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedDateFilter()
    {
        $this->resetPage();
    }

    public function updatedPaymentTypeFilter()
    {
        $this->resetPage();
    }

    public function viewDetails($saleId)
    {
        $this->selectedSale = Sale::with(['customer', 'items.product', 'payments', 'returns.product'])
            ->find($saleId);

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

    /**
     * Open Dummy Bill Generator modal for a sale.
     */
    public function openDummyBillModal($saleId)
    {
        $sale = Sale::with(['customer', 'items.product', 'payments'])->find($saleId);
        if (! $sale) {
            $this->showToast('error', 'Sale not found.');

            return;
        }

        $this->dummySaleId = $sale->id;
        $this->dummyInvoiceNumber = $sale->invoice_number;
        $this->dummyCustomerName = $sale->customer_display_name;
        $this->dummyOriginalTotal = (float) $sale->total_amount;
        $this->dummyTargetTotal = (float) $sale->total_amount;

        $this->dummyItems = [];
        foreach ($sale->items as $item) {
            $qty = max(1, (int) $item->quantity);
            $itemTotal = (float) $item->total;
            $unitPrice = (float) $item->unit_price;

            $this->dummyItems[] = [
                'id' => $item->id,
                'product_name' => $item->product_name,
                'product_code' => $item->product_code ?? '',
                'product_model' => $item->product_model ?? null,
                'quantity' => $qty,
                'original_unit_price' => $unitPrice,
                'original_total' => $itemTotal,
                'unit_price' => $unitPrice,
                'total' => $itemTotal,
                'has_warranty' => ! empty($item->has_warranty),
                'warranty_duration' => $item->warranty_duration ?? null,
            ];
        }

        $this->showDummyModal = true;
    }

    public function closeDummyModal()
    {
        $this->showDummyModal = false;
        $this->dummySaleId = null;
        $this->dummyItems = [];
        $this->dummyTargetTotal = '';
    }

    /**
     * Proportional price reduction algorithm to hit target total exactly.
     */
    public function applyTargetTotal()
    {
        $target = (float) str_replace(',', '', (string) $this->dummyTargetTotal);
        if ($target <= 0) {
            $this->showToast('error', 'Please enter a valid target total amount.');

            return;
        }

        if (empty($this->dummyItems)) {
            $this->showToast('error', 'No products found in this bill.');

            return;
        }

        $totalOriginal = (float) collect($this->dummyItems)->sum('original_total');
        if ($totalOriginal <= 0) {
            $totalOriginal = (float) collect($this->dummyItems)->sum(function ($it) {
                return ((int) $it['quantity']) * ((float) $it['original_unit_price']);
            });
        }

        if ($totalOriginal <= 0) {
            $totalOriginal = 1;
        }

        $ratio = $target / $totalOriginal;

        $allocatedSum = 0;
        $highestIndex = 0;
        $highestTotal = -1;

        foreach ($this->dummyItems as $index => &$item) {
            $qty = max(1, (int) $item['quantity']);
            $origTotal = (float) ($item['original_total'] ?: ($qty * $item['original_unit_price']));
            $scaledTotal = round($origTotal * $ratio);
            $unitPrice = round($scaledTotal / $qty, 2);
            $calculatedLineTotal = round($unitPrice * $qty, 2);

            $item['unit_price'] = $unitPrice;
            $item['total'] = $calculatedLineTotal;
            $allocatedSum += $calculatedLineTotal;

            if ($calculatedLineTotal > $highestTotal) {
                $highestTotal = $calculatedLineTotal;
                $highestIndex = $index;
            }
        }
        unset($item);

        // Zero-penny rounding balancing
        $diff = round($target - $allocatedSum, 2);
        if (abs($diff) > 0 && isset($this->dummyItems[$highestIndex])) {
            $qty = max(1, (int) $this->dummyItems[$highestIndex]['quantity']);
            $this->dummyItems[$highestIndex]['total'] = round($this->dummyItems[$highestIndex]['total'] + $diff, 2);
            $this->dummyItems[$highestIndex]['unit_price'] = round($this->dummyItems[$highestIndex]['total'] / $qty, 2);
        }

        $this->showToast('success', 'Prices reduced to match target total Rs. '.number_format($target, 2));
    }

    public function recalculateItemTotal($index)
    {
        if (isset($this->dummyItems[$index])) {
            $qty = max(1, (int) ($this->dummyItems[$index]['quantity'] ?? 1));
            $unitPrice = (float) str_replace(',', '', (string) ($this->dummyItems[$index]['unit_price'] ?? 0));
            $this->dummyItems[$index]['unit_price'] = $unitPrice;
            $this->dummyItems[$index]['total'] = round($unitPrice * $qty, 2);
        }
    }

    public function updatedDummyItems($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) >= 2 && $parts[1] === 'unit_price') {
            $this->recalculateItemTotal((int) $parts[0]);
        }
    }

    public function getCalculatedDummyTotalProperty(): float
    {
        return (float) collect($this->dummyItems)->sum('total');
    }

    public function confirmAndPrintDummyThermal()
    {
        $calculatedTotal = $this->calculatedDummyTotal;
        if ($calculatedTotal <= 0) {
            $this->showToast('error', 'Total amount must be greater than zero.');

            return;
        }

        session(['dummy_sale_print' => [
            'sale_id' => $this->dummySaleId,
            'invoice_number' => $this->dummyInvoiceNumber,
            'total_amount' => $calculatedTotal,
            'items' => $this->dummyItems,
        ]]);

        $this->js("window.open('".route('shop-staff.print.dummy-sale-thermal')."', '_blank', 'width=350,height=600,scrollbars=yes')");
    }

    public function confirmAndPrintDummyA4()
    {
        $calculatedTotal = $this->calculatedDummyTotal;
        if ($calculatedTotal <= 0) {
            $this->showToast('error', 'Total amount must be greater than zero.');

            return;
        }

        session(['dummy_sale_print' => [
            'sale_id' => $this->dummySaleId,
            'invoice_number' => $this->dummyInvoiceNumber,
            'total_amount' => $calculatedTotal,
            'items' => $this->dummyItems,
        ]]);

        $this->js("window.open('".route('shop-staff.print.dummy-sale-a4')."', '_blank', 'width=800,height=900,scrollbars=yes')");
    }

    /**
     * Redirect to store-billing page with the sale ID for editing
     */
    public function editSale($saleId)
    {
        $sale = Sale::find($saleId);

        if (! $sale) {
            $this->showToast('error', 'Sale not found.');

            return;
        }

        if ($sale->user_id !== Auth::id()) {
            $this->showToast('error', 'You can only edit your own sales.');

            return;
        }

        return redirect()->route('shop-staff.store-billing', ['edit' => $saleId]);
    }

    /**
     * Print invoice
     */
    public function printInvoice($saleId)
    {
        $sale = Sale::find($saleId);
        if (! $sale) {
            $this->showToast('error', 'Sale not found.');

            return;
        }

        session(['print_sale_id' => $sale->id]);

        $this->js("window.open('/shop-staff/print/sale/{$saleId}', '_blank', 'width=350,height=600,scrollbars=yes')");
    }

    public function render()
    {
        $baseQuery = Sale::where('user_id', Auth::id())
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('invoice_number', 'like', '%'.$this->search.'%')
                        ->orWhere('walking_customer_name', 'like', '%'.$this->search.'%')
                        ->orWhere('walking_customer_phone', 'like', '%'.$this->search.'%')
                        ->orWhereHas('customer', function ($cq) {
                            $cq->where('name', 'like', '%'.$this->search.'%')
                                ->orWhere('phone', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->when($this->statusFilter, function ($q) {
                $q->where('payment_status', $this->statusFilter);
            })
            ->when($this->paymentTypeFilter, function ($q) {
                if ($this->paymentTypeFilter === 'multiple') {
                    $q->whereHas('payments', function ($pq) {
                        $pq->select('sale_id')->groupBy('sale_id')->havingRaw('COUNT(DISTINCT payment_method) > 1 OR COUNT(*) > 1');
                    });
                } elseif ($this->paymentTypeFilter === 'card' || $this->paymentTypeFilter === 'online') {
                    $q->whereHas('payments', function ($pq) {
                        $pq->whereIn('payment_method', ['card', 'online']);
                    });
                } else {
                    $q->whereHas('payments', function ($pq) {
                        $pq->where('payment_method', $this->paymentTypeFilter);
                    });
                }
            })
            ->when($this->dateFilter, function ($q) {
                $q->whereDate('created_at', $this->dateFilter);
            });

        $salesListQuery = (clone $baseQuery)->with(['customer', 'payments'])->orderBy('created_at', 'desc');

        $statsQuery = clone $baseQuery;

        $summarySales = $statsQuery->sum('total_amount');
        $summaryCount = $statsQuery->count();

        $sales = $salesListQuery->get();

        if (! \App\Helpers\TaxAuditHelper::isUnlocked()) {
            $filteredSales = collect();
            $runningTotal = 0;
            foreach ($sales as $sale) {
                if ($runningTotal + $sale->total_amount <= 105000 || $filteredSales->isEmpty()) {
                    $filteredSales->push($sale);
                    $runningTotal += $sale->total_amount;
                    if ($runningTotal >= 100000) {
                        break;
                    }
                }
            }
            $sales = $filteredSales;
            $summarySales = $runningTotal;
            $summaryCount = $sales->count();
        }

        return view('livewire.shop-staff.shop-staff-sales-list', [
            'sales' => $sales,
            'summarySales' => $summarySales,
            'summaryCount' => $summaryCount,
        ]);
    }

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
