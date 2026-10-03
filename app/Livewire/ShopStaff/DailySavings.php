<?php

namespace App\Livewire\ShopStaff;

use App\Livewire\Concerns\WithDynamicLayout;
use App\Models\Deposit;
use App\Models\POSSession;
use App\Models\SavingMethod;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Daily Savings & Cash Deposits')]
class DailySavings extends Component
{
    use WithDynamicLayout, WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Filters
    public $selectedMonth;

    public $selectedDate;

    public $dateFilterMode = 'today'; // 'today', 'month', 'custom_date'

    public $methodFilter = 'all';

    public $search = '';

    public $perPage = 15;

    // Financial Metrics - Today
    public $todayCashSales = 0;

    public $todayBankSales = 0;

    public $todayCardSales = 0;

    public $todayTotalSales = 0;

    public $todayDeposited = 0;

    public $todayCashSavings = 0;

    public $todayDirectSaleDeposits = 0;

    public $todayDirectBankDeposits = 0;

    public $todayDirectCardDeposits = 0;

    public $todayRemainingCash = 0;

    public $todaySeeduSavings = 0;

    public $todayUndiyalSavings = 0;

    public $todayBankSavings = 0;

    public $todayTotalAllDeposits = 0;

    // Financial Metrics - Selected Month
    public $monthCashSales = 0;

    public $monthBankSales = 0;

    public $monthCardSales = 0;

    public $monthTotalSales = 0;

    public $monthTotalSavings = 0;

    public $monthCashSavings = 0;

    public $monthDirectBankDeposits = 0;

    public $monthDirectCardDeposits = 0;

    public $monthTotalAllDeposits = 0;

    public $monthSeeduSavings = 0;

    public $monthUndiyalSavings = 0;

    public $monthBankSavings = 0;

    public $monthOtherSavings = 0;

    public $savingsRatio = 0;

    // Add / Edit Deposit Modal
    public $showAddModal = false;

    public $editingDepositId = null;

    public $depositDate;

    public $saving_method = 'seedu';

    public $source_of_funds = 'cash_sale';

    public $reference_no = '';

    public $amount = '';

    public $description = '';

    // Create New Saving Method Modal
    public $showMethodModal = false;

    public $newMethodName = '';

    public $newMethodDescription = '';

    public $newMethodColor = 'primary';

    public $newMethodIcon = 'bi-piggy-bank';

    // Delete Confirmation Modal
    public $showDeleteModal = false;

    public $depositToDelete = null;

    protected function rules()
    {
        return [
            'depositDate' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'saving_method' => 'required|string|max:100',
            'source_of_funds' => 'required|string|max:50',
            'reference_no' => 'nullable|string|max:150',
            'description' => 'nullable|string|max:255',
        ];
    }

    public function mount()
    {
        $today = Carbon::now();
        $this->selectedMonth = $today->format('Y-m');
        $this->selectedDate = $today->toDateString(); // DEFAULT TO TODAY!
        $this->dateFilterMode = 'today';
        $this->depositDate = $today->toDateString();
        $this->ensureDefaultMethods();
        $this->loadFinancialSummary();
    }

    /**
     * Parse selectedMonth string safely to prevent Malformed time string exceptions.
     */
    public function getParsedMonth(): Carbon
    {
        if (empty($this->selectedMonth)) {
            return Carbon::now()->startOfMonth();
        }

        $trimmed = trim($this->selectedMonth);
        if (! preg_match('/^\d{4}-\d{1,2}$/', $trimmed)) {
            return Carbon::now()->startOfMonth();
        }

        try {
            return Carbon::createFromFormat('Y-m', $trimmed)->startOfMonth();
        } catch (\Throwable $e) {
            return Carbon::now()->startOfMonth();
        }
    }

    /**
     * Safely format selectedDate to avoid any date parsing errors
     */
    public function getFormattedSelectedDate(): string
    {
        if (empty($this->selectedDate)) {
            return '';
        }
        try {
            return Carbon::parse($this->selectedDate)->format('d M Y');
        } catch (\Throwable $e) {
            return (string) $this->selectedDate;
        }
    }

    /**
     * Switch view to Today's deposits (Default mode)
     */
    public function filterToday(): void
    {
        $this->selectedDate = Carbon::now()->toDateString();
        $this->dateFilterMode = 'today';
        $this->resetPage();
    }

    /**
     * Switch view to full month or specific selected month
     */
    public function filterMonth(?string $month = null): void
    {
        if ($month && preg_match('/^\d{4}-\d{1,2}$/', trim($month))) {
            $this->selectedMonth = trim($month);
        } elseif (empty($this->selectedMonth)) {
            $this->selectedMonth = Carbon::now()->format('Y-m');
        }
        $this->selectedDate = null;
        $this->dateFilterMode = 'month';
        $this->resetPage();
        $this->loadFinancialSummary();
    }

    public function updatedSelectedMonth()
    {
        if (! empty($this->selectedMonth) && preg_match('/^\d{4}-\d{1,2}$/', trim($this->selectedMonth))) {
            $this->dateFilterMode = 'month';
            $this->selectedDate = null;
        }
        $this->resetPage();
        $this->loadFinancialSummary();
    }

    public function updatedSelectedDate()
    {
        if (! empty($this->selectedDate)) {
            $this->dateFilterMode = ($this->selectedDate === Carbon::now()->toDateString()) ? 'today' : 'custom_date';
        } else {
            $this->dateFilterMode = 'month';
        }
        $this->resetPage();
    }

    public function updatedMethodFilter()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    /**
     * Ensure standard saving methods (Seedu, Undiyal, Bank Deposit) exist in database
     */
    private function ensureDefaultMethods(): void
    {
        if (SavingMethod::count() === 0) {
            SavingMethod::create([
                'name' => 'Seedu',
                'code' => 'seedu',
                'description' => 'Daily / Monthly Chit Fund Saving (சீட்டு)',
                'icon' => 'bi-safe2',
                'color' => 'success',
                'is_active' => true,
            ]);
            SavingMethod::create([
                'name' => 'Undiyal',
                'code' => 'undiyal',
                'description' => 'Daily Undiyal Cash Box Saving (உண்டியல்)',
                'icon' => 'bi-piggy-bank',
                'color' => 'warning',
                'is_active' => true,
            ]);
            SavingMethod::create([
                'name' => 'Bank Deposit',
                'code' => 'bank_deposit',
                'description' => 'Direct Cash Deposit to Bank Account',
                'icon' => 'bi-bank',
                'color' => 'info',
                'is_active' => true,
            ]);
            SavingMethod::create([
                'name' => 'Card / POS Deposit',
                'code' => 'card_deposit',
                'description' => 'Direct Card / Online POS Payment to Bank Account',
                'icon' => 'bi-credit-card-2-front',
                'color' => 'primary',
                'is_active' => true,
            ]);
        } elseif (SavingMethod::where('code', 'card_deposit')->doesntExist()) {
            SavingMethod::create([
                'name' => 'Card / POS Deposit',
                'code' => 'card_deposit',
                'description' => 'Direct Card / Online POS Payment to Bank Account',
                'icon' => 'bi-credit-card-2-front',
                'color' => 'primary',
                'is_active' => true,
            ]);
        }
    }

    /**
     * Load cash sales, bank payments, card payments and savings breakdown
     */
    public function loadFinancialSummary(): void
    {
        try {
            $today = Carbon::now()->toDateString();

            // 1. TODAY'S SALES BREAKDOWN BY PAYMENT METHOD
            $this->todayCashSales = (float) DB::table('payments')
                ->join('sales', 'payments.sale_id', '=', 'sales.id')
                ->where('sales.status', 'confirm')
                ->where('payments.payment_method', 'cash')
                ->whereDate('payments.created_at', $today)
                ->sum('payments.amount');

            $this->todayBankSales = (float) DB::table('payments')
                ->join('sales', 'payments.sale_id', '=', 'sales.id')
                ->where('sales.status', 'confirm')
                ->where('payments.payment_method', 'bank_transfer')
                ->whereDate('payments.created_at', $today)
                ->sum('payments.amount');

            $this->todayCardSales = (float) DB::table('payments')
                ->join('sales', 'payments.sale_id', '=', 'sales.id')
                ->where('sales.status', 'confirm')
                ->where('payments.payment_method', 'online')
                ->whereDate('payments.created_at', $today)
                ->sum('payments.amount');

            $this->todayTotalSales = $this->todayCashSales + $this->todayBankSales + $this->todayCardSales;

            // Today's deposits separated into physical cash savings vs direct electronic deposits
            $todayDepositsQuery = Deposit::whereDate('date', $today);
            $this->todayTotalAllDeposits = (float) (clone $todayDepositsQuery)->sum('amount');

            $todayCashSavingsQuery = (clone $todayDepositsQuery)->where(function ($q) {
                $q->whereNull('source_of_funds')
                    ->orWhere('source_of_funds', 'cash_sale');
            });
            $this->todayCashSavings = (float) (clone $todayCashSavingsQuery)->sum('amount');
            $this->todaySeeduSavings = (float) (clone $todayCashSavingsQuery)->where('saving_method', 'seedu')->sum('amount');
            $this->todayUndiyalSavings = (float) (clone $todayCashSavingsQuery)->where('saving_method', 'undiyal')->sum('amount');
            $this->todayBankSavings = (float) (clone $todayCashSavingsQuery)->where('saving_method', 'bank_deposit')->sum('amount');

            $this->todayDirectBankDeposits = (float) (clone $todayDepositsQuery)
                ->where('saving_method', 'bank_deposit')
                ->where('source_of_funds', 'sale_bank_transfer')
                ->sum('amount');

            $this->todayDirectCardDeposits = (float) (clone $todayDepositsQuery)
                ->where('saving_method', 'card_deposit')
                ->where('source_of_funds', 'sale_card')
                ->sum('amount');

            $this->todayDirectSaleDeposits = $this->todayDirectBankDeposits + $this->todayDirectCardDeposits;
            $this->todayDeposited = $this->todayCashSavings + $this->todayDirectSaleDeposits;
            $this->todayRemainingCash = max(0, $this->todayCashSales - $this->todayCashSavings);

            // 2. SELECTED MONTH'S SALES & SAVINGS BREAKDOWN
            $parsedMonth = $this->getParsedMonth();
            $monthStart = $parsedMonth->copy()->startOfMonth()->startOfDay();
            $monthEnd = $parsedMonth->copy()->endOfMonth()->endOfDay();

            $this->monthCashSales = (float) DB::table('payments')
                ->join('sales', 'payments.sale_id', '=', 'sales.id')
                ->where('sales.status', 'confirm')
                ->where('payments.payment_method', 'cash')
                ->whereBetween('payments.created_at', [$monthStart, $monthEnd])
                ->sum('payments.amount');

            $this->monthBankSales = (float) DB::table('payments')
                ->join('sales', 'payments.sale_id', '=', 'sales.id')
                ->where('sales.status', 'confirm')
                ->where('payments.payment_method', 'bank_transfer')
                ->whereBetween('payments.created_at', [$monthStart, $monthEnd])
                ->sum('payments.amount');

            $this->monthCardSales = (float) DB::table('payments')
                ->join('sales', 'payments.sale_id', '=', 'sales.id')
                ->where('sales.status', 'confirm')
                ->where('payments.payment_method', 'online')
                ->whereBetween('payments.created_at', [$monthStart, $monthEnd])
                ->sum('payments.amount');

            $this->monthTotalSales = $this->monthCashSales + $this->monthBankSales + $this->monthCardSales;

            // Monthly Deposits Query
            $depositsQuery = Deposit::whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()]);
            $this->monthTotalAllDeposits = (float) (clone $depositsQuery)->sum('amount');

            // Physical Cash Savings (Seedu, Undiyal, Cash Deposited to Bank)
            $cashSavingsQuery = (clone $depositsQuery)->where(function ($q) {
                $q->whereNull('source_of_funds')
                    ->orWhere('source_of_funds', 'cash_sale');
            });
            $this->monthCashSavings = (float) (clone $cashSavingsQuery)->sum('amount');
            $this->monthSeeduSavings = (float) (clone $cashSavingsQuery)->where('saving_method', 'seedu')->sum('amount');
            $this->monthUndiyalSavings = (float) (clone $cashSavingsQuery)->where('saving_method', 'undiyal')->sum('amount');
            $this->monthBankSavings = (float) (clone $cashSavingsQuery)->where('saving_method', 'bank_deposit')->sum('amount');
            $this->monthOtherSavings = max(0, $this->monthCashSavings - ($this->monthSeeduSavings + $this->monthUndiyalSavings + $this->monthBankSavings));

            // Direct Electronic Sale Deposits (Bank Transfer Sales & Card POS Sales)
            $this->monthDirectBankDeposits = (float) (clone $depositsQuery)
                ->where('saving_method', 'bank_deposit')
                ->where('source_of_funds', 'sale_bank_transfer')
                ->sum('amount');

            $this->monthDirectCardDeposits = (float) (clone $depositsQuery)
                ->where('saving_method', 'card_deposit')
                ->where('source_of_funds', 'sale_card')
                ->sum('amount');

            $this->monthTotalSavings = $this->monthCashSavings;
            $this->savingsRatio = $this->monthCashSales > 0
                ? round(($this->monthCashSavings / $this->monthCashSales) * 100, 1)
                : 0;

        } catch (\Exception $e) {
            Log::error('DailySavings summary calculation error: '.$e->getMessage());
        }
    }

    /**
     * Modal management
     */
    public function openAddModal()
    {
        $this->resetForm();
        $this->depositDate = Carbon::now()->format('Y-m-d');
        $this->showAddModal = true;
    }

    public function closeAddModal()
    {
        $this->showAddModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->editingDepositId = null;
        $this->depositDate = Carbon::now()->format('Y-m-d');
        $this->saving_method = 'seedu';
        $this->source_of_funds = 'cash_sale';
        $this->reference_no = '';
        $this->amount = '';
        $this->description = '';
        $this->resetErrorBag();
    }

    public function setAmountPreset(float $value)
    {
        $this->amount = $value;
    }

    public function addAmountPreset(float $value)
    {
        $current = (float) ($this->amount ?: 0);
        $this->amount = $current + $value;
    }

    /**
     * Save or Update Deposit Record
     */
    public function saveDeposit()
    {
        $this->validate();

        $data = [
            'date' => $this->depositDate,
            'amount' => (float) $this->amount,
            'saving_method' => $this->saving_method,
            'source_of_funds' => $this->source_of_funds,
            'reference_no' => $this->reference_no,
            'user_id' => Auth::id(),
            'status' => 'completed',
            'description' => $this->description ?: (ucfirst($this->saving_method).' daily deposit'),
        ];

        if ($this->editingDepositId) {
            $deposit = Deposit::findOrFail($this->editingDepositId);
            $deposit->update($data);
            $message = 'Deposit record updated successfully!';
        } else {
            Deposit::create($data);
            $message = 'Daily deposit of Rs. '.number_format($this->amount, 2).' saved successfully!';
        }

        // Sync POS session cash_deposit_bank for this day
        $totalDepositsToday = Deposit::whereDate('date', $this->depositDate)->sum('amount');
        POSSession::where('session_date', $this->depositDate)
            ->update(['cash_deposit_bank' => $totalDepositsToday]);

        $this->closeAddModal();
        $this->loadFinancialSummary();
        $this->dispatch('deposit-saved', ['message' => $message]);
        $this->js("Swal.fire({icon: 'success', title: 'Saved!', text: '$message', timer: 2000, showConfirmButton: false});");
    }

    /**
     * Edit an existing deposit
     */
    public function editDeposit(int $id)
    {
        $deposit = Deposit::findOrFail($id);
        $this->editingDepositId = $deposit->id;
        $this->depositDate = $deposit->date ? $deposit->date->format('Y-m-d') : Carbon::now()->format('Y-m-d');
        $this->saving_method = $deposit->saving_method ?: 'seedu';
        $this->source_of_funds = $deposit->source_of_funds ?: 'cash_sale';
        $this->reference_no = $deposit->reference_no;
        $this->amount = (float) $deposit->amount;
        $this->description = $deposit->description;
        $this->showAddModal = true;
    }

    /**
     * Confirm Delete
     */
    public function confirmDelete(int $id)
    {
        $this->depositToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->depositToDelete = null;
    }

    public function deleteDeposit()
    {
        if ($this->depositToDelete) {
            $deposit = Deposit::find($this->depositToDelete);
            if ($deposit) {
                $targetDate = $deposit->date ? $deposit->date->format('Y-m-d') : null;
                $deposit->delete();

                if ($targetDate) {
                    $totalDepositsToday = Deposit::whereDate('date', $targetDate)->sum('amount');
                    POSSession::where('session_date', $targetDate)
                        ->update(['cash_deposit_bank' => $totalDepositsToday]);
                }
            }
        }

        $this->closeDeleteModal();
        $this->loadFinancialSummary();
        $this->js("Swal.fire({icon: 'success', title: 'Deleted!', text: 'Deposit record has been removed.', timer: 1800, showConfirmButton: false});");
    }

    /**
     * Modal for Creating New Saving Method
     */
    public function openMethodModal()
    {
        $this->newMethodName = '';
        $this->newMethodDescription = '';
        $this->newMethodColor = 'primary';
        $this->newMethodIcon = 'bi-piggy-bank';
        $this->showMethodModal = true;
    }

    public function closeMethodModal()
    {
        $this->showMethodModal = false;
    }

    public function saveNewMethod()
    {
        $this->validate([
            'newMethodName' => 'required|string|min:2|max:100|unique:saving_methods,name',
            'newMethodDescription' => 'nullable|string|max:255',
            'newMethodColor' => 'required|string',
        ]);

        $code = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', trim($this->newMethodName)));

        $method = SavingMethod::create([
            'name' => trim($this->newMethodName),
            'code' => $code,
            'description' => $this->newMethodDescription,
            'icon' => $this->newMethodIcon ?: 'bi-piggy-bank',
            'color' => $this->newMethodColor ?: 'primary',
            'is_active' => true,
        ]);

        $this->saving_method = $method->code;
        $this->closeMethodModal();
        $this->js("Swal.fire({icon: 'success', title: 'Saving Method Created!', text: 'Method \"{$method->name}\" is ready to use.', timer: 1800, showConfirmButton: false});");
    }

    public function render()
    {
        $parsedMonth = $this->getParsedMonth();
        $monthStart = $parsedMonth->copy()->startOfMonth()->toDateString();
        $monthEnd = $parsedMonth->copy()->endOfMonth()->toDateString();

        $query = Deposit::with('user');

        if ($this->dateFilterMode === 'today') {
            $query->whereDate('date', Carbon::now()->toDateString());
        } elseif ($this->dateFilterMode === 'custom_date' && ! empty($this->selectedDate)) {
            $query->whereDate('date', $this->selectedDate);
        } else {
            $query->whereBetween('date', [$monthStart, $monthEnd]);
            if (! empty($this->selectedDate)) {
                $query->whereDate('date', $this->selectedDate);
            }
        }

        if ($this->methodFilter && $this->methodFilter !== 'all') {
            if ($this->methodFilter === 'cash_savings') {
                $query->where(function ($q) {
                    $q->whereNull('source_of_funds')
                        ->orWhere('source_of_funds', 'cash_sale');
                });
            } elseif ($this->methodFilter === 'direct_sales') {
                $query->whereIn('source_of_funds', ['sale_bank_transfer', 'sale_card']);
            } else {
                $query->where('saving_method', $this->methodFilter);
            }
        }

        if ($this->search) {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('reference_no', 'like', "%{$s}%")
                    ->orWhere('description', 'like', "%{$s}%")
                    ->orWhere('amount', 'like', "%{$s}%")
                    ->orWhere('saving_method', 'like', "%{$s}%");
            });
        }

        $deposits = $query->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);

        $savingMethods = SavingMethod::where('is_active', true)->get();

        return view('livewire.shop-staff.daily-savings', [
            'deposits' => $deposits,
            'savingMethods' => $savingMethods,
        ])->layout($this->layout);
    }
}
