<?php

namespace App\Livewire\ShopStaff;

use App\Models\Sale;
use App\Models\Payment;
use App\Models\Customer;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

#[Title('Due Sales')]
#[Layout('components.layouts.shop-staff')]
class ShopStaffDueSales extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $dateFilter = '';

    // Payment Modal
    public $showPaymentModal = false;
    public $selectedSaleId = null;
    public $paymentMethod = 'cash';
    public $paymentAmount = 0;
    public $bankReference = '';
    public $bankName = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedDateFilter()
    {
        $this->resetPage();
    }

    public function openPaymentModal($saleId)
    {
        $sale = Sale::with('customer')->find($saleId);
        if (!$sale) {
            $this->showToast('error', 'Sale not found.');
            return;
        }

        $this->selectedSaleId = $saleId;
        $this->paymentMethod = 'cash';
        $this->paymentAmount = round($sale->due_amount, 2);
        $this->bankReference = '';
        $this->bankName = '';
        
        $this->showPaymentModal = true;
    }

    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
        $this->selectedSaleId = null;
        $this->paymentAmount = 0;
        $this->bankReference = '';
        $this->bankName = '';
    }

    public function getSelectedSaleProperty()
    {
        if ($this->selectedSaleId) {
            return Sale::with('customer')->find($this->selectedSaleId);
        }
        return null;
    }

    public function processPayment()
    {
        $selectedSale = $this->selectedSale;
        if (!$selectedSale) return;

        $this->validate([
            'paymentMethod' => 'required|in:cash,online,bank_transfer',
            'paymentAmount' => 'required|numeric|min:1|max:' . floatval($selectedSale->due_amount),
            'bankReference' => 'required_if:paymentMethod,bank_transfer,online',
        ]);

        try {
            DB::beginTransaction();

            $amount = floatval($this->paymentAmount);

            // Create Payment Record
            $payment = Payment::create([
                'customer_id' => $selectedSale->customer_id,
                'sale_id' => $selectedSale->id,
                'amount' => $amount,
                'payment_method' => $this->paymentMethod,
                'due_payment_method' => $this->paymentMethod,
                'payment_date' => now(),
                'is_completed' => true,
                'status' => 'paid',
                'payment_reference' => $this->paymentMethod === 'cash' ? 'CASH-' . now()->format('YmdHis') : $this->bankReference,
                'bank_name' => in_array($this->paymentMethod, ['bank_transfer', 'online']) ? $this->bankName : null,
            ]);

            // Update Sale
            $selectedSale->due_amount -= $amount;
            
            if ($selectedSale->due_amount <= 0) {
                $selectedSale->payment_status = 'paid';
            } else {
                $selectedSale->payment_status = 'partial';
            }
            
            $selectedSale->save();

            // Update Customer if not walking customer
            if ($selectedSale->customer && $selectedSale->customer->name !== 'Walking Customer') {
                $selectedSale->customer->due_amount -= $amount;
                if ($selectedSale->customer->due_amount < 0) {
                    $selectedSale->customer->due_amount = 0;
                }
                $selectedSale->customer->save();
            }

            DB::commit();

            $this->showToast('success', 'Payment processed successfully.');
            $this->closePaymentModal();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->showToast('error', 'Error processing payment: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $query = Sale::where('due_amount', '>', 0)
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('invoice_number', 'like', '%' . $this->search . '%')
                        ->orWhere('walking_customer_name', 'like', '%' . $this->search . '%')
                        ->orWhere('walking_customer_phone', 'like', '%' . $this->search . '%')
                        ->orWhereHas('customer', function ($cq) {
                            $cq->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('phone', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->dateFilter, function ($q) {
                $q->whereDate('created_at', $this->dateFilter);
            });

        return view('livewire.shop-staff.shop-staff-due-sales', [
            'sales' => $query->with(['customer', 'payments'])->orderBy('created_at', 'desc')->paginate(15),
            'totalDue' => $query->clone()->sum('due_amount')
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
