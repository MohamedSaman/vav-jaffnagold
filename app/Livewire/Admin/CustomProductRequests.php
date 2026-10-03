<?php

namespace App\Livewire\Admin;

use App\Models\CustomProductInquiry;
use App\Services\SmsService;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Title('Custom Jewelry Requests & Inquiries')]
#[Layout('components.layouts.admin')]
class CustomProductRequests extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Filters
    public $search = '';
    public $statusFilter = '';
    public $categoryFilter = '';
    public $perPage = 15;

    // Detail & Action Modal
    public $selectedInquiryId = null;
    public $selectedInquiry = null;
    public $showDetailModal = false;
    public $quotePrice = '';
    public $adminNotes = '';
    public $updateStatus = 'new';
    public $smsSentMessage = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'categoryFilter' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter()
    {
        $this->resetPage();
    }

    public function openDetail($id)
    {
        $this->selectedInquiryId = $id;
        $this->selectedInquiry = CustomProductInquiry::with('customProduct')->findOrFail($id);
        $this->quotePrice = $this->selectedInquiry->quoted_price 
            ? (float) $this->selectedInquiry->quoted_price 
            : (float) $this->selectedInquiry->estimated_price;
        $this->adminNotes = $this->selectedInquiry->admin_notes ?? '';
        $this->updateStatus = $this->selectedInquiry->status;
        $this->smsSentMessage = '';
        $this->showDetailModal = true;
    }

    public function closeDetail()
    {
        $this->showDetailModal = false;
        $this->selectedInquiryId = null;
        $this->selectedInquiry = null;
        $this->smsSentMessage = '';
    }

    public function updateInquiryStatus($id, $newStatus)
    {
        $inquiry = CustomProductInquiry::findOrFail($id);
        $inquiry->status = $newStatus;
        $inquiry->save();

        if ($this->selectedInquiry && $this->selectedInquiry->id === $id) {
            $this->selectedInquiry->status = $newStatus;
            $this->updateStatus = $newStatus;
        }

        $this->dispatch('swal:alert', [
            'icon' => 'success',
            'title' => 'Status Updated',
            'text' => "Inquiry #{$inquiry->inquiry_number} status updated to " . ucfirst(str_replace('_', ' ', $newStatus)) . '.',
        ]);
    }

    public function saveQuoteAndNotes()
    {
        if (!$this->selectedInquiryId) {
            return;
        }

        $this->validate([
            'quotePrice' => 'nullable|numeric|min:0',
            'adminNotes' => 'nullable|string|max:2000',
            'updateStatus' => 'required|in:new,contacted,quoted,in_crafting,completed,cancelled',
        ]);

        $inquiry = CustomProductInquiry::findOrFail($this->selectedInquiryId);
        $inquiry->quoted_price = $this->quotePrice !== '' ? $this->quotePrice : null;
        $inquiry->admin_notes = $this->adminNotes ?: null;
        $inquiry->status = $this->updateStatus;
        $inquiry->save();

        $this->selectedInquiry = $inquiry->fresh(['customProduct']);

        $this->js("Swal.fire('Saved!', 'Quotation and status updated successfully.', 'success')");
    }

    public function sendSmsNotification()
    {
        if (!$this->selectedInquiry) {
            return;
        }

        $inquiry = $this->selectedInquiry;
        $phone = $inquiry->customer_phone;
        $price = $inquiry->quoted_price ? number_format($inquiry->quoted_price, 0) : number_format($inquiry->estimated_price, 0);

        $message = "Vanakkam {$inquiry->customer_name}, Update regarding your Jaffna Gold custom request ({$inquiry->inquiry_number}) for {$inquiry->product_name} ({$inquiry->selected_pown} Pown). Quoted Price: LKR {$price}. Status: " . ucfirst(str_replace('_', ' ', $inquiry->status)) . ". Hotline: +94 21 222 2222";

        $result = SmsService::sendSms($phone, $message);

        if ($result['success']) {
            $this->smsSentMessage = 'SMS notification sent successfully to customer!';
            $this->js("Swal.fire('SMS Sent!', 'Customer has been notified via SMS.', 'success')");
        } else {
            $this->smsSentMessage = 'Failed to send SMS: ' . ($result['message'] ?? 'Unknown error');
            $this->js("Swal.fire('SMS Error', 'Could not send SMS: " . addslashes($result['message'] ?? 'Check SMS Gateway') . "', 'warning')");
        }
    }

    public function getWhatsAppUrl($inquiry): string
    {
        if (!$inquiry) {
            return '#';
        }

        $phone = preg_replace('/[^0-9]/', '', $inquiry->customer_phone ?? '');
        if (str_starts_with($phone, '0')) {
            $phone = '94' . substr($phone, 1);
        } elseif (!str_starts_with($phone, '94')) {
            $phone = '94' . $phone;
        }

        $quote = $inquiry->quoted_price 
            ? 'LKR ' . number_format($inquiry->quoted_price, 0) 
            : 'LKR ' . number_format($inquiry->estimated_price, 0);

        $text = "வணக்கம் / Vanakkam {$inquiry->customer_name}! 🌟\n"
              . "Thank you for inquiring with *Jaffna Gold Master Goldsmiths*.\n\n"
              . "📋 *Inquiry Ref:* {$inquiry->inquiry_number}\n"
              . "💍 *Item:* {$inquiry->product_name}\n"
              . "⚖️ *Weight:* {$inquiry->selected_pown} Pown ({$inquiry->selected_grams}g) 22K (916)\n"
              . ($inquiry->size_specifications ? "📏 *Specifications:* {$inquiry->size_specifications}\n" : "")
              . "💰 *Quoted Estimate:* {$quote}\n\n"
              . "How would you like to proceed with your order?";

        return 'https://wa.me/' . $phone . '?text=' . rawurlencode($text);
    }

    public function render()
    {
        $query = CustomProductInquiry::with('customProduct')->latest();

        if ($this->search) {
            $s = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('inquiry_number', 'like', $s)
                  ->orWhere('customer_name', 'like', $s)
                  ->orWhere('customer_phone', 'like', $s)
                  ->orWhere('city', 'like', $s)
                  ->orWhere('product_name', 'like', $s);
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->categoryFilter) {
            $query->where('category', $this->categoryFilter);
        }

        $inquiries = $query->paginate($this->perPage);

        // Overall KPI Counts
        $totalCount = CustomProductInquiry::count();
        $newCount = CustomProductInquiry::where('status', 'new')->count();
        $quotedCount = CustomProductInquiry::where('status', 'quoted')->count();
        $inCraftingCount = CustomProductInquiry::where('status', 'in_crafting')->count();
        $completedCount = CustomProductInquiry::where('status', 'completed')->count();

        return view('livewire.admin.custom-product-requests', [
            'inquiries' => $inquiries,
            'totalCount' => $totalCount,
            'newCount' => $newCount,
            'quotedCount' => $quotedCount,
            'inCraftingCount' => $inCraftingCount,
            'completedCount' => $completedCount,
        ]);
    }
}
