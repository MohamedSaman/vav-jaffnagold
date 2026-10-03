<?php

namespace App\Livewire\ShopStaff;

use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('My Quotations')]
#[Layout('components.layouts.shop-staff')]
class ShopStaffQuotationList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';

    public $perPage = 10;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    /**
     * Print quotation thermal style
     */
    public function printQuotation($id)
    {
        $quotation = Quotation::find($id);
        if (! $quotation) {
            $this->showToast('error', 'Quotation not found.');

            return;
        }

        $this->js("window.open('/shop-staff/print/quotation/{$id}', '_blank', 'width=350,height=600,scrollbars=yes')");
    }

    /**
     * Download quotation PDF
     */
    public function downloadQuotation($id)
    {
        $quotation = Quotation::find($id);
        if (! $quotation) {
            $this->showToast('error', 'Quotation not found.');

            return;
        }

        try {
            if (is_string($quotation->items)) {
                $quotation->items = json_decode($quotation->items, true);
            }

            $pdf = PDF::loadView('admin.quotations.print', compact('quotation'));
            $pdf->setPaper('a4', 'portrait');
            $pdf->setOption('dpi', 150);
            $pdf->setOption('defaultFont', 'sans-serif');

            return response()->streamDownload(
                function () use ($pdf) {
                    echo $pdf->output();
                },
                'quotation-'.$quotation->quotation_number.'.pdf'
            );
        } catch (\Exception $e) {
            $this->showToast('error', 'Failed to generate PDF: '.$e->getMessage());
        }
    }

    /**
     * Redirect to store-billing and preload quotation details to convert it into a sale
     */
    public function convertToSale($id)
    {
        $quotation = Quotation::find($id);
        if (! $quotation) {
            $this->showToast('error', 'Quotation not found.');

            return;
        }

        if ($quotation->status === 'converted') {
            $this->showToast('warning', 'Quotation has already been converted.');

            return;
        }

        return redirect()->route('shop-staff.store-billing', ['convert_quotation' => $id]);
    }

    /**
     * Delete quotation
     */
    public function deleteQuotation($id)
    {
        $quotation = Quotation::find($id);
        if (! $quotation) {
            $this->showToast('error', 'Quotation not found.');

            return;
        }

        if ($quotation->status === 'converted') {
            $this->showToast('error', 'Cannot delete a converted quotation.');

            return;
        }

        $quotation->delete();
        $this->showToast('success', 'Quotation deleted successfully.');
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

    public function render()
    {
        $quotations = Quotation::where('created_by', Auth::id())
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('quotation_number', 'like', '%'.$this->search.'%')
                        ->orWhere('customer_name', 'like', '%'.$this->search.'%')
                        ->orWhere('customer_phone', 'like', '%'.$this->search.'%')
                        ->orWhere('items', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.shop-staff.shop-staff-quotation-list', [
            'quotations' => $quotations,
        ]);
    }
}
