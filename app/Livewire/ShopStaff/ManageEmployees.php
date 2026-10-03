<?php

namespace App\Livewire\ShopStaff;

use Exception;
use App\Models\Employee;
use App\Models\CashAdvance;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Livewire\Concerns\WithDynamicLayout;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

#[Layout('components.layouts.shop-staff')]
#[Title('Manage Employee')]
class ManageEmployees extends Component
{
    use WithDynamicLayout;
    use WithPagination;

    // Create Form Properties
    public $name;
    public $contactNumber;
    public $basic_salary = 0;
    public $status = 'active';

    // Edit Form Properties
    public $editEmployeeId;
    public $editName;
    public $editContactNumber;
    public $editBasicSalary;
    public $editStatus;

    // Component View States
    public $deleteId;
    public $showEditModal = false;
    public $showCreateModal = false;
    public $showDeleteModal = false;
    public $showDetailsModal = false;
    public $perPage = 10;
    public $viewUserDetail = [];
    public $viewEmployeeData = [];

    // Inline View Swapping
    public $payrollView = 1;
    public $activeHistoryEmployee = null;

    // Payroll Form Properties
    public $selectedEmployeeId;
    public $cashAmount;
    public $cashDate;
    public $cashNote;
    public $payOffAmount;
    public $payOffDate;
    public $payrollSummary = [];
    public $historyData = [];

    // Partial Payment Properties
    public $showPartialPaymentModal = false;
    public $partialPaymentEmployeeId = null;
    public $partialPaymentEmployeeName = null;
    public $partialPaymentMaxAmount = 0;
    public $partialPaymentAmount = '';
    public $partialPaymentNote = '';
    public $partialPaymentDate = '';

    public function getListeners()
    {
        return [
            'employee-data-updated' => 'refreshEmployeeData',
            '$refresh' => '$refresh'
        ];
    }

    public function refreshEmployeeData()
    {
        $this->resetPage();
    }

    public function render()
    {
        $localToday = Carbon::today();
        $month = $localToday->month;
        $year  = $localToday->year;

        $employees = Employee::where('status', 'active')
            ->latest()
            ->paginate($this->perPage)
            ->through(function ($employee) use ($month, $year) {
                // Get draws (expenses/advances that are NOT salary payments)
                $draws = (float)$employee->cashAdvances()
                    ->where('month', $month)
                    ->where('year', $year)
                    ->where('note', 'not like', '%salary%')
                    ->sum('amount');
                
                // Get salary payments
                $payments = (float)$employee->cashAdvances()
                    ->where('month', $month)
                    ->where('year', $year)
                    ->where('note', 'salary')
                    ->sum('amount');
                
                $prevOverdue = $employee->getPreviousOverdue($month, $year);
                
                // Count unique working days (days with draws)
                $workingDays = $employee->cashAdvances()
                    ->where('month', $month)
                    ->where('year', $year)
                    ->where('note', 'not like', '%salary%')
                    ->distinct('date')
                    ->count('date');

                // Calculate net pay: (daily rate × working days) - total draws
                if ($workingDays > 0) {
                    $netPay = ($employee->basic_salary * $workingDays) - $draws;
                } else {
                    $netPay = 0;
                }
                
                $netPay = max(0, $netPay);
                
                // Balance amount (what shows in Awaiting Payment column)
                $employee->balance_amount = $prevOverdue + $netPay - $payments;
                $employee->awaiting_payments = max(0, $employee->balance_amount);
                
                // Generate initials for avatar
                $words = explode(' ', $employee->name);
                $employee->initials = strtoupper(substr($words[0] ?? 'E', 0, 1) . substr($words[1] ?? '', 0, 1));

                return $employee;
            });

        return view('livewire.shop-staff.manage-employees', [
            'employees' => $employees,
        ])->layout($this->layout);
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function closeModal()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
        $this->showDetailsModal = false;
        $this->showPartialPaymentModal = false;
        $this->viewEmployeeData = [];
        $this->resetForm();
        $this->resetPartialPaymentForm();
    }

    public function viewEmployeeDetails($id)
    {
        $employee = Employee::find($id);
        if (!$employee) {
            $this->js("Swal.fire('Error!', 'Employee Not Found', 'error')");
            return;
        }
        $this->viewEmployeeData = [
            'name'         => $employee->name,
            'contact'      => $employee->contact,
            'basic_salary' => $employee->basic_salary,
            'status'       => $employee->status,
            'joined'       => $employee->created_at->format('d M, Y'),
        ];
        $this->showDetailsModal = true;
    }

    public function resetForm()
    {
        $this->reset([
            'name', 'contactNumber', 'basic_salary', 'status',
            'editEmployeeId', 'editName', 'editContactNumber', 
            'editBasicSalary', 'editStatus', 'deleteId'
        ]);
        $this->resetErrorBag();
    }

    public function resetPartialPaymentForm()
    {
        $this->partialPaymentEmployeeId = null;
        $this->partialPaymentEmployeeName = null;
        $this->partialPaymentMaxAmount = 0;
        $this->partialPaymentAmount = '';
        $this->partialPaymentNote = '';
        $this->partialPaymentDate = '';
        $this->resetErrorBag();
    }

    public function createEmployee()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function saveEmployee()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'contactNumber' => 'required|max:15',
            'basic_salary' => 'required|numeric|min:0',
        ]);

        try {
            Employee::create([
                'name' => $this->name,
                'contact' => $this->contactNumber,
                'basic_salary' => $this->basic_salary,
                'status' => 'active',
            ]);

            $this->js("Swal.fire('Success!', 'Employee Created Successfully', 'success')");
            $this->closeModal();
            $this->resetPage();
        } catch (Exception $e) {
            $this->js("Swal.fire('Error!', '" . $e->getMessage() . "', 'error')");
        }
    }

    public function editEmployee($id)
    {
        $employee = Employee::find($id);
        if (!$employee) {
            $this->js("Swal.fire('Error!', 'Employee Not Found', 'error')");
            return;
        }

        $this->editEmployeeId = $employee->id;
        $this->editName = $employee->name;
        $this->editContactNumber = $employee->contact;
        $this->editBasicSalary = $employee->basic_salary;
        $this->editStatus = $employee->status;

        $this->showEditModal = true;
    }

    public function updateEmployee()
    {
        $this->validate([
            'editName' => 'required|string|max:255',
            'editContactNumber' => 'required|max:15',
            'editBasicSalary' => 'required|numeric|min:0',
            'editStatus' => 'required|in:active,inactive',
        ]);

        try {
            $employee = Employee::find($this->editEmployeeId);
            if ($employee) {
                $employee->update([
                    'name' => $this->editName,
                    'contact' => $this->editContactNumber,
                    'basic_salary' => $this->editBasicSalary,
                    'status' => $this->editStatus,
                ]);

                $this->js("Swal.fire('Success!', 'Employee Updated Successfully', 'success')");
                $this->closeModal();
            }
        } catch (Exception $e) {
            $this->js("Swal.fire('Error!', '" . $e->getMessage() . "', 'error')");
        }
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteEmployee()
    {
        try {
            $employee = Employee::find($this->deleteId);
            if ($employee) {
                $employee->update(['status' => 'inactive']);
                $this->js("Swal.fire('Success!', 'Employee deleted successfully.', 'success')");
            }
            $this->closeModal();
            $this->resetPage();
        } catch (Exception $e) {
            $this->js("Swal.fire('Error!', '" . $e->getMessage() . "', 'error')");
        }
    }

    // Opens the payment modal when clicking the green Pay button
    public function openPartialPayment($employeeId)
    {
        $employee = Employee::find($employeeId);
        if (!$employee) {
            $this->js("Swal.fire('Error!', 'Employee Not Found', 'error')");
            return;
        }

        $localToday = Carbon::today();
        $month = $localToday->month;
        $year = $localToday->year;
        
        // Calculate current balance
        $draws = (float)$employee->cashAdvances()
            ->where('month', $month)
            ->where('year', $year)
            ->where('note', 'not like', '%salary%')
            ->sum('amount');
        
        $payments = (float)$employee->cashAdvances()
            ->where('month', $month)
            ->where('year', $year)
            ->where('note', 'salary')
            ->sum('amount');
        
        $prevOverdue = $employee->getPreviousOverdue($month, $year);
        
        $workingDays = $employee->cashAdvances()
            ->where('month', $month)
            ->where('year', $year)
            ->where('note', 'not like', '%salary%')
            ->distinct('date')
            ->count('date');
        
        if ($workingDays > 0) {
            $netPay = ($employee->basic_salary * $workingDays) - $draws;
        } else {
            $netPay = 0;
        }
        $netPay = max(0, $netPay);
        
        $balanceAmount = $prevOverdue + $netPay - $payments;
        
        if ($balanceAmount <= 0) {
            $this->js("Swal.fire('Info', 'No outstanding balance due for this employee.', 'info')");
            return;
        }

        $this->partialPaymentEmployeeId = $employee->id;
        $this->partialPaymentEmployeeName = $employee->name;
        $this->partialPaymentMaxAmount = $balanceAmount;
        $this->partialPaymentAmount = '';
        $this->partialPaymentDate = Carbon::today()->format('Y-m-d');
        $this->showPartialPaymentModal = true;
    }

    // Process the payment from the modal
    public function processPartialPayment()
    {
        $this->validate([
            'partialPaymentAmount' => 'required|numeric|min:0.01|max:' . $this->partialPaymentMaxAmount,
            'partialPaymentDate' => 'required|date',
        ]);

        try {
            $employee = Employee::find($this->partialPaymentEmployeeId);
            if (!$employee) {
                $this->js("Swal.fire('Error!', 'Employee not found.', 'error')");
                return;
            }

            $paymentDate = Carbon::parse($this->partialPaymentDate);
            $month = $paymentDate->month;
            $year = $paymentDate->year;
            $paymentAmount = floatval($this->partialPaymentAmount);
            $paymentDateFormatted = $paymentDate->format('Y-m-d');

            // Create cash advance record
            $payment = CashAdvance::create([
                'employee_id' => $employee->id,
                'amount' => $paymentAmount,
                'date' => $paymentDateFormatted,
                'note' => 'salary',
                'month' => $month,
                'year' => $year,
            ]);

            $this->js("Swal.fire('Success!', 'Payment of Rs. " . number_format($paymentAmount, 2) . " processed successfully!', 'success')");
            $this->closeModal();
            
            // Refresh the view
            if ($this->payrollView == 2 && $this->activeHistoryEmployee && $this->activeHistoryEmployee->id == $employee->id) {
                $this->switchPayrollView(2, $employee->id);
            } else {
                $this->dispatch('$refresh');
            }

        } catch (\Exception $e) {
            \Log::error('Payment processing error', ['error' => $e->getMessage()]);
            $this->js("Swal.fire('Error!', '" . addslashes($e->getMessage()) . "', 'error')");
        }
    }

    // Switch between View 1 (employee list) and View 2 (history)
    public function switchPayrollView($viewId, $employeeId = null)
    {
        $this->payrollView = $viewId;

        if ($viewId == 2 && $employeeId) {
            $employee = Employee::find($employeeId);
            if (!$employee) {
                $this->js("Swal.fire('Error!', 'Employee Not Found', 'error')");
                $this->payrollView = 1;
                return;
            }

            $words = explode(' ', $employee->name);
            $employee->initials = strtoupper(substr($words[0] ?? 'E', 0, 1) . substr($words[1] ?? '', 0, 1));
            $this->activeHistoryEmployee = $employee;
            
            // Get ALL transactions using direct query
            $allTransactions = DB::table('cash_advances')
                ->where('employee_id', $employeeId)
                ->orderBy('date', 'asc')
                ->orderBy('id', 'asc')
                ->get();
            
            $dailyRate = $employee->basic_salary;
            $runningBalance = 0;
            $historyItems = [];
            $datesWithSalary = [];
            
            foreach ($allTransactions as $transaction) {
                if (empty($transaction->date)) {
                    continue;
                }
                
                try {
                    $transactionDate = Carbon::parse($transaction->date);
                    $dateKey = $transactionDate->format('Y-m-d');
                    $formattedDate = $transactionDate->format('d M Y');
                    
                    // Determine if payment (note is exactly 'salary')
                    $isPayment = ($transaction->note === 'salary');
                    $isDraw = !$isPayment;
                    
                    // Set details text
                    $detailsText = '';
                    if ($isDraw) {
                        $detailsText = !empty($transaction->note) ? $transaction->note : 'Daily wage';
                        // Clean up common patterns
                        $detailsText = str_replace('Daily Pay: Payment to ABDR', 'Daily Pay', $detailsText);
                        $detailsText = str_replace('Daily Pay:', 'Daily Pay', $detailsText);
                    } elseif ($isPayment) {
                        $detailsText = 'Salary Payment';
                    }
                    
                    // Add salary only once per day for draws
                    $salaryForThisTransaction = 0;
                    if ($isDraw && !in_array($dateKey, $datesWithSalary)) {
                        $salaryForThisTransaction = $dailyRate;
                        $datesWithSalary[] = $dateKey;
                    }
                    
                    // Update running balance
                    $runningBalance = $runningBalance + $salaryForThisTransaction - $transaction->amount;
                    $currentBalance = max(0, $runningBalance);
                    
                    // Style for payment rows
                    $rowBackgroundColor = $isPayment ? '#b45309' : '';
                    $rowTextColor = $isPayment ? '#ffffff' : '';
                    
                    $historyItems[] = [
                        'date' => $formattedDate,
                        'details' => $detailsText,
                        'salary' => $salaryForThisTransaction,
                        'drawn_amount' => ($isDraw ? (float)$transaction->amount : 0),
                        'payment_amount' => ($isPayment ? (float)$transaction->amount : 0),
                        'balance' => $currentBalance,
                        'awaiting_payments' => $currentBalance,
                        'is_payment' => $isPayment,
                        'is_draw' => $isDraw,
                        'background_color' => $rowBackgroundColor,
                        'text_color' => $rowTextColor,
                    ];
                    
                } catch (\Exception $e) {
                    continue;
                }
            }
            
            $this->historyData = $historyItems;
        }
    }
}