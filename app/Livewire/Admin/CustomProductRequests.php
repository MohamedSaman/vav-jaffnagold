<?php

namespace App\Livewire\Admin;

use App\Models\CustomProductInquiry;
use App\Services\SmsService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

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
            'text' => "Inquiry #{$inquiry->inquiry_number} status updated to ".ucfirst(str_replace('_', ' ', $newStatus)).'.',
        ]);
    }

    public function saveQuoteAndNotes()
    {
        if (! $this->selectedInquiryId) {
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
        if (! $this->selectedInquiry) {
            return;
        }

        $inquiry = $this->selectedInquiry;
        $phone = $inquiry->customer_phone;
        $branchName = config('shop.branch', 'Vavuniya Branch');
        $hotline = config('shop.hotline', '021 222 85 89');
        $message = "Vanakkam {$inquiry->customer_name}, Update regarding your Jaffna Gold ({$branchName}) custom request ({$inquiry->inquiry_number}) for {$inquiry->product_name} ({$inquiry->selected_pown} Pown). Quoted Price: LKR {$price}. Status: ".ucfirst(str_replace('_', ' ', $inquiry->status)).". Hotline: {$hotline}";

        $isLow = SmsService::isBalanceLow();
        $isFinished = SmsService::isBalanceFinished();
        $balance = SmsService::getBalance();
        $rate = SmsService::getPerSmsRate();
        $contact = SmsService::getTopupContact();

        if (($isLow || $isFinished) && ! request()->boolean('force_double', false)) {
            $balFmt = number_format($balance, 2);
            $doubleRate = number_format($rate * 2, 2);
            $regRate = number_format($rate, 2);
            $stateTitle = $isFinished ? 'Finished' : 'Low';
            $stateLabel = $isFinished ? 'exhausted' : 'low';

            $this->js(<<<JS
Swal.fire({
    title: '⚠️ SMS Balance {$stateTitle}!',
    html: `
        <div class="text-start">
            <p class="mb-2 fw-semibold text-danger">⚠️ SMS account balance is {$stateLabel}!</p>
            <div class="alert alert-warning p-2 mb-3 small">
                <div><strong>Current Balance:</strong> Rs. {$balFmt}</div>
                <div><strong>Regular Rate:</strong> Rs. {$regRate} per SMS</div>
                <div class="text-danger fw-bold mt-1">⚠️ If you continue, double charge (Rs. {$doubleRate}) will apply.</div>
            </div>
            <p class="mb-0 small text-muted">Click <strong>Top Up Request</strong> to request a credit recharge directly to <strong>{$contact}</strong> via SMS, or continue with double charge.</p>
        </div>
    `,
    icon: 'warning',
    showCancelButton: true,
    showDenyButton: true,
    confirmButtonText: '<i class="bi bi-send-exclamation me-1"></i> Continue (Double Charge)',
    denyButtonText: '<i class="bi bi-chat-text-fill me-1"></i> Top Up Request via SMS',
    cancelButtonText: '<i class="bi bi-x-circle me-1"></i> Cancel',
    confirmButtonColor: '#dc3545',
    denyButtonColor: '#0d6efd',
    cancelButtonColor: '#6c757d',
}).then((result) => {
    if (result.isConfirmed) {
        \$wire.sendSmsWithDoubleCharge({$inquiry->id});
    } else if (result.isDenied) {
        \$wire.requestSmsTopup();
    }
});
JS);

            return;
        }

        $result = SmsService::sendSms($phone, $message);

        if ($result['success']) {
            $this->smsSentMessage = 'SMS notification sent successfully to customer!';
            $this->js("Swal.fire('SMS Sent!', 'Customer has been notified via SMS.', 'success')");
        } else {
            $this->smsSentMessage = 'Failed to send SMS: '.($result['message'] ?? 'Unknown error');
            $this->js("Swal.fire('SMS Error', 'Could not send SMS: ".addslashes($result['message'] ?? 'Check SMS Gateway')."', 'warning')");
        }
    }

    public function requestSmsTopup()
    {
        $contact = SmsService::getTopupContact();
        $balance = SmsService::getBalance();
        $user = auth()->user()?->name ?? 'Admin';
        $smsMsg = 'Top-Up Request: Jaffna Gold (Vavuniya Branch) requested balance recharge (Current Bal: Rs. '.number_format($balance, 2).") by {$user}.";

        try {
            SmsService::sendSms($contact, $smsMsg, false, false);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('[Vav] Top-up SMS request failed: '.$e->getMessage());
        }

        $this->js("Swal.fire('Request Sent via SMS!', 'Your SMS top-up request has been dispatched to helpline ({$contact}). Once credited, your balance will update automatically.', 'success')");
    }

    public function sendSmsWithDoubleCharge($inquiryId)
    {
        $inquiry = CustomProductInquiry::findOrFail($inquiryId);
        $phone = $inquiry->customer_phone;
        if (empty($phone)) {
            $this->js("Swal.fire('Error', 'No customer phone number available', 'error')");

            return;
        }

        $price = number_format($inquiry->admin_estimated_price ?? 0, 2);
        $branchName = config('shop.branch', 'Vavuniya Branch');
        $hotline = config('shop.hotline', '021 222 85 89');
        $message = "Vanakkam {$inquiry->customer_name}, Update regarding your Jaffna Gold ({$branchName}) custom request ({$inquiry->inquiry_number}) for {$inquiry->product_name} ({$inquiry->selected_pown} Pown). Quoted Price: LKR {$price}. Status: ".ucfirst(str_replace('_', ' ', $inquiry->status)).". Hotline: {$hotline}";

        $result = SmsService::sendSms($phone, $message, true);

        if ($result['success']) {
            $this->smsSentMessage = 'SMS notification sent successfully to customer! (Double charge applied)';
            $this->js("Swal.fire('SMS Sent!', 'Customer has been notified via SMS (Double charge applied).', 'success')");
        } else {
            $this->smsSentMessage = 'Failed to send SMS: '.($result['message'] ?? 'Unknown error');
            $this->js("Swal.fire('SMS Error', 'Could not send SMS: ".addslashes($result['message'] ?? 'Check SMS Gateway')."', 'warning')");
        }
    }

    public function getWhatsAppUrl($inquiry): string
    {
        if (! $inquiry) {
            return '#';
        }

        $phone = preg_replace('/[^0-9]/', '', $inquiry->customer_phone ?? '');
        if (str_starts_with($phone, '0')) {
            $phone = '94'.substr($phone, 1);
        } elseif (! str_starts_with($phone, '94')) {
            $phone = '94'.$phone;
        }

        $quote = $inquiry->quoted_price
            ? 'LKR '.number_format($inquiry->quoted_price, 0)
            : 'LKR '.number_format($inquiry->estimated_price, 0);

        $text = "வணக்கம் / Vanakkam {$inquiry->customer_name}! 🌟\n"
              ."Thank you for inquiring with *Jaffna Gold Master Goldsmiths*.\n\n"
              ."📋 *Inquiry Ref:* {$inquiry->inquiry_number}\n"
              ."💍 *Item:* {$inquiry->product_name}\n"
              ."⚖️ *Weight:* {$inquiry->selected_pown} Pown ({$inquiry->selected_grams}g) 22K (916)\n"
              .($inquiry->size_specifications ? "📏 *Specifications:* {$inquiry->size_specifications}\n" : '')
              ."💰 *Quoted Estimate:* {$quote}\n\n"
              .'How would you like to proceed with your order?';

        return 'https://wa.me/'.$phone.'?text='.rawurlencode($text);
    }

    public function render()
    {
        $query = CustomProductInquiry::with('customProduct')->latest();

        if ($this->search) {
            $s = '%'.trim($this->search).'%';
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
