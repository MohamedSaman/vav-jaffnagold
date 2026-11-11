<?php

namespace App\Services;

use App\Models\HrEmployeeSetting;
use App\Models\HrSalary;
use App\Models\HrSalaryPayment;
use App\Models\ZkEmployee;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HrService
{
    /**
     * Get or create default employee settings for an employee.
     */
    public function getOrCreateSetting(ZkEmployee $employee): HrEmployeeSetting
    {
        return HrEmployeeSetting::firstOrCreate(
            ['emp_code' => (string) $employee->emp_code],
            [
                'zk_employee_id' => $employee->id,
                'salary_type' => 'monthly',
                'daily_working_hours' => 8.00,
                'basic_salary' => 0.00,
                'daily_rate' => 0.00,
                'hourly_rate' => 0.00,
                'overtime_rate' => 0.00,
                'allowance' => 0.00,
                'opening_balance' => 0.00,
            ]
        );
    }

    /**
     * Update employee settings (daily hours, salary type, rates).
     */
    public function updateEmployeeSetting(string $empCode, array $data): HrEmployeeSetting
    {
        $employee = ZkEmployee::where('emp_code', $empCode)->firstOrFail();
        $existingSetting = HrEmployeeSetting::where('emp_code', $empCode)->first();

        $openingBalance = array_key_exists('opening_balance', $data)
            ? (float) $data['opening_balance']
            : (float) ($existingSetting?->opening_balance ?? 0.00);

        return HrEmployeeSetting::updateOrCreate(
            ['emp_code' => $empCode],
            [
                'zk_employee_id' => $employee->id,
                'salary_type' => $data['salary_type'] ?? 'monthly',
                'daily_working_hours' => (float) ($data['daily_working_hours'] ?? 8.00),
                'basic_salary' => (float) ($data['basic_salary'] ?? 0.00),
                'daily_rate' => (float) ($data['daily_rate'] ?? 0.00),
                'hourly_rate' => (float) ($data['hourly_rate'] ?? 0.00),
                'overtime_rate' => (float) ($data['overtime_rate'] ?? 0.00),
                'allowance' => (float) ($data['allowance'] ?? 0.00),
                'opening_balance' => $openingBalance,
                'notes' => $data['notes'] ?? null,
            ]
        );
    }

    /**
     * Detect if an expense type / category / description refers to a staff member's salary
     * (e.g. "SAMAN SALARY", "SALMAN SALARY", "MUATH SALARY", "Salary for Saman", etc.)
     */
    public function detectSalaryEmployee(?string $type, ?string $description = null): ?ZkEmployee
    {
        $combined = strtoupper(trim(($type ?? '').' '.($description ?? '')));
        if ($combined === '') {
            return null;
        }

        // Check if text mentions SALARY / SALERY / WAGE / ADVANCE or matches "{NAME} SALARY"
        $hasSalaryKeyword = (bool) preg_match('/\b(SALARY|SALERY|SALARIES|WAGE|WAGES|ADVANCE)\b/i', $combined);
        if (! $hasSalaryKeyword) {
            return null;
        }

        $employees = ZkEmployee::with(['department', 'hrSetting'])
            ->orderByRaw('CAST(emp_code AS UNSIGNED) ASC')
            ->get();

        // Sort longer names first to avoid partial matches, and use word boundaries (\b)
        $sortedEmployees = $employees->sortByDesc(fn ($e) => strlen(trim($e->full_name)));

        foreach ($sortedEmployees as $emp) {
            $fullName = strtoupper(trim($emp->full_name));
            $firstName = strtoupper(trim($emp->first_name ?? ''));

            if ($fullName !== '' && preg_match('/\b'.preg_quote($fullName, '/').'\b/i', $combined)) {
                return $emp;
            }
            if ($firstName !== '' && preg_match('/\b'.preg_quote($firstName, '/').'\b/i', $combined)) {
                return $emp;
            }
        }

        return null;
    }

    /**
     * Get live salary & attendance snapshot for an employee on a specific date.
     * Includes:
     * - Today's worked hours and attendance-calculated salary
     * - Previous saved balance (unpaid balance from earlier days + opening balance)
     * - Paid today
     * - Total available salary balance (Previous Saved Balance + Today Salary - Paid Today)
     */
    public function getEmployeeSalarySnapshot(string $empCode, ?string $date = null): array
    {
        $dateObj = $date ? Carbon::parse($date)->toDateString() : Carbon::now()->toDateString();
        $yearMonth = Carbon::parse($dateObj)->format('Y-m');
        $salaryMonth = Carbon::parse($dateObj)->startOfMonth()->toDateString();

        // Ensure monthly salary record is synced with latest punches
        $this->syncMonthlySalaries($yearMonth);

        $daily = $this->getDailyAttendance($dateObj, (string) $empCode)->first();
        $monthSalary = HrSalary::where('emp_code', (string) $empCode)
            ->where('salary_month', $salaryMonth)
            ->first();

        if (! $daily) {
            $emp = ZkEmployee::with(['department', 'hrSetting'])->where('emp_code', (string) $empCode)->first();

            return [
                'emp_code' => (string) $empCode,
                'employee_name' => $emp?->full_name ?? "Employee #{$empCode}",
                'department' => $emp?->department?->dept_name ?? 'Staff',
                'date' => $dateObj,
                'year_month' => $yearMonth,
                'hours_worked' => 0.00,
                'expected_hours' => 10.00,
                'first_in' => null,
                'last_out' => null,
                'punch_count' => 0,
                'status' => 'absent',
                'is_live' => false,
                'day_full_salary' => 0.00,
                'hourly_rate' => 0.00,
                'today_salary' => 0.00,
                'paid_today' => 0.00,
                'today_balance' => 0.00,
                'opening_balance' => (float) ($monthSalary?->opening_balance ?? 0.00),
                'earned_before_today' => 0.00,
                'paid_before_today' => 0.00,
                'previous_balance' => (float) ($monthSalary?->balance_amount ?? 0.00),
                'total_available_balance' => (float) ($monthSalary?->balance_amount ?? 0.00),
                'month_net_salary' => (float) ($monthSalary?->net_salary ?? 0.00),
                'month_paid_amount' => (float) ($monthSalary?->paid_amount ?? 0.00),
                'month_balance' => (float) ($monthSalary?->balance_amount ?? 0.00),
                'hr_salary_id' => $monthSalary?->id,
            ];
        }

        $emp = $daily['employee'];

        return [
            'emp_code' => (string) $empCode,
            'employee_name' => $emp?->full_name ?? "Employee #{$empCode}",
            'department' => $emp?->department?->dept_name ?? 'Staff',
            'date' => $dateObj,
            'year_month' => $yearMonth,
            'hours_worked' => (float) $daily['hours_worked'],
            'expected_hours' => (float) $daily['expected_hours'],
            'first_in' => $daily['first_in'],
            'last_out' => $daily['last_out'],
            'punch_count' => (int) $daily['punch_count'],
            'status' => $daily['status'],
            'is_live' => (bool) ($daily['is_live'] ?? false),
            'day_full_salary' => (float) $daily['day_full_salary'],
            'hourly_rate' => (float) $daily['hourly_rate'],
            'today_salary' => (float) $daily['today_salary'],
            'paid_today' => (float) $daily['paid_today'],
            'today_balance' => (float) $daily['today_balance'],
            'opening_balance' => (float) ($daily['opening_balance'] ?? 0.00),
            'earned_before_today' => (float) ($daily['earned_before_today'] ?? 0.00),
            'paid_before_today' => (float) ($daily['paid_before_today'] ?? 0.00),
            'previous_balance' => (float) ($daily['previous_balance'] ?? 0.00),
            'total_available_balance' => (float) ($daily['total_available_balance'] ?? 0.00),
            'month_net_salary' => (float) ($monthSalary?->net_salary ?? 0.00),
            'month_paid_amount' => (float) ($monthSalary?->paid_amount ?? 0.00),
            'month_balance' => (float) ($monthSalary?->balance_amount ?? 0.00),
            'hr_salary_id' => $monthSalary?->id,
        ];
    }

    /**
     * Get raw punch transactions date-wise for a specific date or date range.
     */
    public function getRawTimeLogs(?string $date = null, ?string $startDate = null, ?string $endDate = null, ?string $empCode = null)
    {
        $query = DB::table('iclock_transaction as t')
            ->leftJoin('personnel_employee as e', 't.emp_code', '=', 'e.emp_code')
            ->leftJoin('personnel_department as d', 'e.department_id', '=', 'd.id')
            ->select(
                't.id',
                't.emp_code',
                't.punch_time',
                't.punch_state',
                't.verify_type',
                't.terminal_id',
                't.terminal_alias',
                't.area_alias',
                'e.first_name',
                'e.last_name',
                'd.dept_name'
            );

        if ($date) {
            $startOfDay = Carbon::parse($date)->startOfDay()->toDateTimeString();
            $endOfDay = Carbon::parse($date)->endOfDay()->toDateTimeString();
            $query->whereBetween('t.punch_time', [$startOfDay, $endOfDay]);
        } elseif ($startDate && $endDate) {
            $query->whereBetween('t.punch_time', [
                Carbon::parse($startDate)->startOfDay()->toDateTimeString(),
                Carbon::parse($endDate)->endOfDay()->toDateTimeString(),
            ]);
        }

        if ($empCode) {
            $query->where('t.emp_code', $empCode);
        }

        return $query->orderBy('t.punch_time', 'desc')->get();
    }

    /**
     * Process raw punches into alternating IN/OUT pairs and calculate exact session working time.
     * 1st punch = IN, 2nd punch = OUT, 3rd punch = IN, 4th punch = OUT, etc.
     */
    public function processPunchSessions($punches, ?string $date = null): array
    {
        $sorted = collect($punches)->sortBy('punch_time')->values();
        $count = $sorted->count();
        $totalMinutes = 0;
        $sessions = [];
        $labeledPunches = [];

        for ($i = 0; $i < $count; $i++) {
            $p = $sorted[$i];
            $timeObj = Carbon::parse(is_object($p) ? $p->punch_time : ($p['punch_time'] ?? $p));
            $isOdd = ($i % 2 === 0); // 0, 2, 4 -> IN; 1, 3, 5 -> OUT
            $type = $isOdd ? 'IN' : 'OUT';
            $sessionNum = (int) floor($i / 2) + 1;

            $labeledPunches[] = [
                'id' => is_object($p) ? ($p->id ?? null) : ($p['id'] ?? null),
                'punch_time' => $timeObj->toDateTimeString(),
                'time' => $timeObj->format('H:i:s'),
                'type' => $type,
                'session' => $sessionNum,
            ];
        }

        $firstPunchTime = $count > 0 ? Carbon::parse($labeledPunches[0]['punch_time']) : null;
        $isToday = $date ? (Carbon::parse($date)->toDateString() === Carbon::now()->toDateString()) : ($firstPunchTime ? $firstPunchTime->isToday() : false);
        $isLiveSession = false;

        // Calculate session durations (Pairing i and i+1)
        for ($i = 0; $i < $count; $i += 2) {
            $inItem = $labeledPunches[$i];
            $outItem = $labeledPunches[$i + 1] ?? null;

            if ($outItem) {
                $inTime = Carbon::parse($inItem['punch_time']);
                $outTime = Carbon::parse($outItem['punch_time']);
                $diff = max(0, $outTime->diffInMinutes($inTime));
                $totalMinutes += $diff;

                $sessions[] = [
                    'session' => $inItem['session'],
                    'in_id' => $inItem['id'],
                    'in_time' => $inItem['time'],
                    'out_id' => $outItem['id'],
                    'out_time' => $outItem['time'],
                    'duration_minutes' => $diff,
                    'duration_hours' => round($diff / 60, 2),
                    'is_completed' => true,
                    'is_running' => false,
                ];
            } else {
                // If odd punch (clocked IN without OUT) and date is today, calculate live ongoing duration up to now
                $diff = 0;
                $isRunning = false;
                if ($isToday) {
                    $inTime = Carbon::parse($inItem['punch_time']);
                    $diff = max(0, Carbon::now()->diffInMinutes($inTime));
                    $totalMinutes += $diff;
                    $isRunning = true;
                    $isLiveSession = true;
                }

                $sessions[] = [
                    'session' => $inItem['session'],
                    'in_id' => $inItem['id'],
                    'in_time' => $inItem['time'],
                    'out_id' => null,
                    'out_time' => $isRunning ? 'Live' : null,
                    'duration_minutes' => $diff,
                    'duration_hours' => round($diff / 60, 2),
                    'is_completed' => false,
                    'is_running' => $isRunning,
                ];
            }
        }

        $totalHours = round($totalMinutes / 60, 2);
        $firstIn = $count > 0 ? $labeledPunches[0]['time'] : null;
        $lastOut = null;
        if ($count > 1) {
            $lastOut = ($count % 2 === 0)
                ? $labeledPunches[$count - 1]['time']
                : $labeledPunches[$count - 2]['time'];
        }

        return [
            'total_minutes' => $totalMinutes,
            'total_hours' => $totalHours,
            'first_in' => $firstIn,
            'last_out' => $lastOut,
            'is_live' => $isLiveSession,
            'sessions' => $sessions,
            'labeled_punches' => $labeledPunches,
        ];
    }

    /**
     * Get punches for an employee on a specific date.
     */
    public function getPunchesForDate(string $empCode, string $date)
    {
        $startOfDay = Carbon::parse($date)->startOfDay()->toDateTimeString();
        $endOfDay = Carbon::parse($date)->endOfDay()->toDateTimeString();

        return DB::table('iclock_transaction')
            ->where('emp_code', $empCode)
            ->whereBetween('punch_time', [$startOfDay, $endOfDay])
            ->orderBy('punch_time', 'asc')
            ->get();
    }

    /**
     * Update a punch timestamp.
     */
    public function updatePunchTime(int $punchId, string $newDateTime): bool
    {
        return DB::table('iclock_transaction')
            ->where('id', $punchId)
            ->update([
                'punch_time' => Carbon::parse($newDateTime)->toDateTimeString(),
            ]) > 0;
    }

    /**
     * Add a new punch for an employee.
     */
    public function addPunch(string $empCode, string $dateTime): int
    {
        $employee = ZkEmployee::where('emp_code', $empCode)->first();
        $dt = Carbon::parse($dateTime);

        return DB::table('iclock_transaction')->insertGetId([
            'emp_code' => $empCode,
            'punch_time' => $dt->toDateTimeString(),
            'punch_state' => '0',
            'verify_type' => 1,
            'emp_id' => $employee?->id ?? 0,
            'terminal_id' => 1,
            'terminal_alias' => 'Manual Entry',
            'area_alias' => 'Main Shop',
            'upload_time' => Carbon::now()->toDateTimeString(),
        ]);
    }

    /**
     * Delete a punch record.
     */
    public function deletePunch(int $punchId): bool
    {
        return DB::table('iclock_transaction')->where('id', $punchId)->delete() > 0;
    }

    /**
     * Process daily attendance for all employees on a given date.
     */
    public function getDailyAttendance(string $date, ?string $empCode = null): Collection
    {
        $dateObj = Carbon::parse($date)->toDateString();
        $startOfDay = Carbon::parse($date)->startOfDay()->toDateTimeString();
        $endOfDay = Carbon::parse($date)->endOfDay()->toDateTimeString();
        $startOfMonthDate = Carbon::parse($date)->startOfMonth()->toDateString();
        $startOfMonthDateTime = Carbon::parse($date)->startOfMonth()->startOfDay()->toDateTimeString();

        $employeesQuery = ZkEmployee::with(['department', 'hrSetting', 'areas']);

        if ($empCode) {
            $employeesQuery->where('emp_code', $empCode);
        }

        $employees = $employeesQuery->orderBy('emp_code')->get();

        // Get all punches for this date range - leverages B-tree index scan on punch_time
        $punchesQuery = DB::table('iclock_transaction')
            ->whereBetween('punch_time', [$startOfDay, $endOfDay]);

        if ($empCode) {
            $punchesQuery->where('emp_code', $empCode);
        }

        $allPunches = $punchesQuery->orderBy('punch_time', 'asc')
            ->get()
            ->groupBy('emp_code');

        // Get prior punches in the same month (strictly before $dateObj) to compute saved balance
        $priorMonthPunches = collect();
        if ($dateObj > $startOfMonthDate) {
            $priorEndDateTime = Carbon::parse($dateObj)->subDay()->endOfDay()->toDateTimeString();
            $priorPunchesQuery = DB::table('iclock_transaction')
                ->whereBetween('punch_time', [$startOfMonthDateTime, $priorEndDateTime]);

            if ($empCode) {
                $priorPunchesQuery->where('emp_code', $empCode);
            }

            $priorMonthPunches = $priorPunchesQuery->orderBy('punch_time', 'asc')
                ->get()
                ->groupBy(function ($punch) {
                    return $punch->emp_code.'_'.Carbon::parse($punch->punch_time)->toDateString();
                });
        }

        // Fetch monthly HrSalary rows for this month (for opening balance / adjustments)
        $monthSalariesQuery = HrSalary::where('salary_month', $startOfMonthDate);
        if ($empCode) {
            $monthSalariesQuery->where('emp_code', $empCode);
        }
        $monthSalaries = $monthSalariesQuery->get()->keyBy('emp_code');

        // Fetch salary payments recorded on this date
        $paymentsQuery = DB::table('hr_salary_payments')
            ->where('payment_date', $dateObj);

        if ($empCode) {
            $paymentsQuery->where('emp_code', $empCode);
        }

        $todayPayments = $paymentsQuery->get()->groupBy('emp_code');

        // Fetch salary payments recorded prior to this date in the same month (or linked to this month's HrSalary)
        $monthSalaryIds = $monthSalaries->pluck('id')->filter()->values()->toArray();
        $priorPaymentsQuery = DB::table('hr_salary_payments')
            ->where('payment_date', '<', $dateObj)
            ->where(function ($q) use ($startOfMonthDate, $monthSalaryIds) {
                $q->where('payment_date', '>=', $startOfMonthDate);
                if (! empty($monthSalaryIds)) {
                    $q->orWhereIn('hr_salary_id', $monthSalaryIds);
                }
            });

        if ($empCode) {
            $priorPaymentsQuery->where('emp_code', $empCode);
        }

        $priorPaymentsByEmp = $priorPaymentsQuery->get()->groupBy('emp_code');

        $result = collect();

        foreach ($employees as $emp) {
            $code = (string) $emp->emp_code;
            $empPunches = $allPunches->get($code, collect());
            $setting = $emp->hrSetting;
            $expectedHours = (float) ($setting?->daily_working_hours ?: 10.00);

            // Calculate Day Full Salary rate
            $dayFullSalary = 0.00;
            if ($setting) {
                if ((float) $setting->daily_rate > 0) {
                    $dayFullSalary = (float) $setting->daily_rate;
                } elseif ((float) $setting->basic_salary > 0) {
                    $dayFullSalary = (float) $setting->basic_salary;
                } elseif ((float) $setting->hourly_rate > 0) {
                    $dayFullSalary = round((float) $setting->hourly_rate * 10, 2);
                }
            }

            // Effective hourly rate based on 10-hour standard
            $effectiveHourlyRate = ($dayFullSalary > 0)
                ? round($dayFullSalary / 10.0, 2)
                : ((float) ($setting?->hourly_rate ?? 0) > 0 ? (float) $setting->hourly_rate : 0.00);

            // Calculate earned salary on previous days of this month (< $dateObj)
            $earnedBeforeToday = 0.00;
            if ($dateObj > $startOfMonthDate) {
                $cursor = Carbon::parse($startOfMonthDate);
                $yesterday = Carbon::parse($dateObj)->subDay();
                while ($cursor->lte($yesterday)) {
                    $dKey = $code.'_'.$cursor->toDateString();
                    $dPunches = $priorMonthPunches->get($dKey, collect());
                    if ($dPunches->isNotEmpty()) {
                        $dSession = $this->processPunchSessions($dPunches, $cursor->toDateString());
                        $dHours = (float) $dSession['total_hours'];
                        if ($dHours >= 10.0) {
                            $earnedBeforeToday += $dayFullSalary;
                        } elseif ($dHours > 0) {
                            $earnedBeforeToday += round($dHours * $effectiveHourlyRate, 2);
                        }
                    }
                    $cursor->addDay();
                }
            }
            $earnedBeforeToday = round($earnedBeforeToday, 2);

            // Opening balance & adjustments for the month
            $mSal = $monthSalaries->get($code);
            $openingBalance = ($mSal && $mSal->opening_balance !== null && (float) $mSal->opening_balance > 0)
                ? (float) $mSal->opening_balance
                : (float) ($setting?->opening_balance ?? 0.00);
            $adjustments = $mSal
                ? ((float) $mSal->allowance + (float) $mSal->bonus - (float) $mSal->deductions - (float) $mSal->advance_taken)
                : 0.00;

            $paidBeforeToday = round((float) $priorPaymentsByEmp->get($code, collect())->sum('amount'), 2);
            $previousBalance = round($openingBalance + $earnedBeforeToday + $adjustments - $paidBeforeToday, 2);

            // Today's paid amount so far
            $empPayments = $todayPayments->get($code, collect());
            $paidToday = round((float) $empPayments->sum('amount'), 2);

            if ($empPunches->isEmpty()) {
                $totalAvailableBalance = round($previousBalance - $paidToday, 2);

                $result->push([
                    'emp_code' => $code,
                    'employee' => $emp,
                    'date' => $dateObj,
                    'first_in' => null,
                    'last_out' => null,
                    'punch_count' => 0,
                    'hours_worked' => 0.00,
                    'expected_hours' => $expectedHours,
                    'overtime_hours' => 0.00,
                    'day_full_salary' => $dayFullSalary,
                    'hourly_rate' => $effectiveHourlyRate,
                    'today_salary' => 0.00,
                    'paid_today' => $paidToday,
                    'today_balance' => 0.00,
                    'opening_balance' => $openingBalance,
                    'earned_before_today' => $earnedBeforeToday,
                    'paid_before_today' => $paidBeforeToday,
                    'previous_balance' => $previousBalance,
                    'total_available_balance' => $totalAvailableBalance,
                    'today_payment_status' => ($paidToday > 0 ? 'paid' : 'unpaid'),
                    'is_live' => false,
                    'status' => 'absent',
                    'punches' => [],
                    'sessions' => [],
                    'today_payments' => $empPayments,
                ]);

                continue;
            }

            $sessionData = $this->processPunchSessions($empPunches, $dateObj);
            $hoursWorked = $sessionData['total_hours'];
            $firstIn = $sessionData['first_in'];
            $lastOut = $sessionData['last_out'];

            // SALARY WORKED HOURS 10+ ONLY WE GIVE DAY FULL SALARY, WE DONT HAVE OT HOURS AND SALARY NOW
            $overtime = 0.00;
            if ($hoursWorked <= 0) {
                $todaySalary = 0.00;
            } elseif ($hoursWorked >= 10.0) {
                // 10+ hours: Full Day Salary
                $todaySalary = $dayFullSalary;
            } else {
                // Worked hour salary pro-rated
                $todaySalary = round($hoursWorked * $effectiveHourlyRate, 2);
            }

            $todayBalance = max(0, round($todaySalary - $paidToday, 2));
            $totalAvailableBalance = round($previousBalance + $todaySalary - $paidToday, 2);

            $todayPaymentStatus = 'unpaid';
            if ($paidToday > 0 && $todayBalance <= 0.01) {
                $todayPaymentStatus = 'paid';
            } elseif ($paidToday > 0) {
                $todayPaymentStatus = 'partial';
            }

            $status = 'present';
            if ($empPunches->count() === 1 || ($empPunches->count() % 2 !== 0)) {
                $status = 'checked_in';
            } elseif ($hoursWorked >= 10.0) {
                $status = 'full_day';
            } elseif ($hoursWorked < 5.0) {
                $status = 'half_day';
            }

            $result->push([
                'emp_code' => $code,
                'employee' => $emp,
                'date' => $dateObj,
                'first_in' => $firstIn,
                'last_out' => $lastOut,
                'punch_count' => $empPunches->count(),
                'hours_worked' => $hoursWorked,
                'expected_hours' => $expectedHours,
                'overtime_hours' => $overtime,
                'day_full_salary' => $dayFullSalary,
                'hourly_rate' => $effectiveHourlyRate,
                'today_salary' => $todaySalary,
                'paid_today' => $paidToday,
                'today_balance' => $todayBalance,
                'opening_balance' => $openingBalance,
                'earned_before_today' => $earnedBeforeToday,
                'paid_before_today' => $paidBeforeToday,
                'previous_balance' => $previousBalance,
                'total_available_balance' => $totalAvailableBalance,
                'today_payment_status' => $todayPaymentStatus,
                'is_live' => $sessionData['is_live'] ?? false,
                'status' => $status,
                'punches' => $sessionData['labeled_punches'],
                'sessions' => $sessionData['sessions'],
                'today_payments' => $empPayments,
            ]);
        }

        return $result;
    }

    /**
     * Get monthly attendance summary for an employee or all employees.
     */
    public function getMonthlyAttendanceSummary(string $yearMonth, ?string $empCode = null): Collection
    {
        $startOfMonth = Carbon::parse($yearMonth.'-01')->startOfMonth();
        $endOfMonth = Carbon::parse($yearMonth.'-01')->endOfMonth();

        $employeesQuery = ZkEmployee::with(['department', 'hrSetting']);

        if ($empCode) {
            $employeesQuery->where('emp_code', $empCode);
        }

        $employees = $employeesQuery->orderBy('emp_code')->get();

        // Get punches for the month
        $punchesQuery = DB::table('iclock_transaction')
            ->whereBetween('punch_time', [$startOfMonth, $endOfMonth]);

        if ($empCode) {
            $punchesQuery->where('emp_code', $empCode);
        }

        $allPunches = $punchesQuery->orderBy('punch_time', 'asc')
            ->get()
            ->groupBy(function ($punch) {
                return $punch->emp_code.'_'.Carbon::parse($punch->punch_time)->toDateString();
            });

        $summaries = collect();

        foreach ($employees as $emp) {
            $code = (string) $emp->emp_code;
            $setting = $emp->hrSetting ?? $this->getOrCreateSetting($emp);
            $dailyHours = (float) ($setting->daily_working_hours ?: 10.00);

            // Day Full Salary rate
            $dayFullSalary = 0.00;
            if ((float) $setting->daily_rate > 0) {
                $dayFullSalary = (float) $setting->daily_rate;
            } elseif ((float) $setting->basic_salary > 0) {
                $dayFullSalary = (float) $setting->basic_salary;
            } elseif ((float) $setting->hourly_rate > 0) {
                $dayFullSalary = round((float) $setting->hourly_rate * 10, 2);
            }

            $effectiveHourlyRate = ($dayFullSalary > 0)
                ? round($dayFullSalary / 10.0, 2)
                : ((float) $setting->hourly_rate > 0 ? (float) $setting->hourly_rate : 0.00);

            $workDays = 0;
            $totalHours = 0.00;
            $overtimeHours = 0.00;
            $totalEarnedSalary = 0.00;
            $dailyDetails = [];

            // Loop through each day of the month up to today (or end of month)
            $current = $startOfMonth->copy();
            $now = Carbon::now();
            $lastDayToCheck = $endOfMonth->isFuture() ? min($now, $endOfMonth) : $endOfMonth;

            while ($current->lte($lastDayToCheck)) {
                $dateKey = $code.'_'.$current->toDateString();
                $dayPunches = $allPunches->get($dateKey, collect());

                if ($dayPunches->isNotEmpty()) {
                    $workDays++;
                    $sessionData = $this->processPunchSessions($dayPunches, $current->toDateString());
                    $dayHours = $sessionData['total_hours'];
                    $dayOt = 0.00;

                    $totalHours += $dayHours;
                    $overtimeHours += $dayOt;

                    // Calculate day earned salary based on 10-hour full day rule
                    $dayEarned = 0.00;
                    if ($dayHours >= 10.0) {
                        $dayEarned = $dayFullSalary;
                    } elseif ($dayHours > 0) {
                        $dayEarned = round($dayHours * $effectiveHourlyRate, 2);
                    }
                    $totalEarnedSalary += $dayEarned;

                    $dailyDetails[] = [
                        'date' => $current->toDateString(),
                        'day' => $current->format('D'),
                        'first_in' => $sessionData['first_in'],
                        'last_out' => $sessionData['last_out'] ?: '-',
                        'hours' => $dayHours,
                        'ot' => 0.00,
                        'today_salary' => $dayEarned,
                        'status' => ($dayHours >= 10.0 ? 'full_day' : 'present'),
                        'sessions' => $sessionData['sessions'],
                    ];
                }

                $current->addDay();
            }

            $expectedHours = round($workDays * $dailyHours, 2);

            $summaries->push([
                'emp_code' => $code,
                'employee' => $emp,
                'setting' => $setting,
                'work_days' => $workDays,
                'total_hours' => round($totalHours, 2),
                'expected_hours' => $expectedHours,
                'overtime_hours' => 0.00,
                'total_earned_salary' => round($totalEarnedSalary, 2),
                'daily_details' => $dailyDetails,
            ]);
        }

        return $summaries;
    }

    /**
     * Calculate and sync salary records for a given month.
     * Generates or updates hr_salaries based on employee settings and attendance punches.
     */
    public function syncMonthlySalaries(string $yearMonth): Collection
    {
        $salaryMonth = Carbon::parse($yearMonth.'-01')->toDateString();
        $attendanceSummaries = $this->getMonthlyAttendanceSummary($yearMonth);

        $salaryList = collect();

        foreach ($attendanceSummaries as $item) {
            $emp = $item['employee'];
            $code = $item['emp_code'];
            $setting = $item['setting'];

            $workDays = $item['work_days'];
            $totalHours = $item['total_hours'];
            $expectedHours = $item['expected_hours'];
            $overtimeHours = 0.00;

            $salaryType = $setting->salary_type ?: 'monthly';
            $dailyWorkingHours = (float) ($setting->daily_working_hours ?: 10.00);
            $dailyRate = (float) $setting->daily_rate;
            $hourlyRate = (float) $setting->hourly_rate;
            $basicSalarySetting = (float) $setting->basic_salary;
            $overtimeRate = 0.00;
            $allowance = (float) $setting->allowance;

            // Calculate basic salary based on daily attendance working hours (10h+ = full day rate, <10h = pro-rated)
            $calculatedBasic = 0.00;
            if ($workDays <= 0 && $totalHours <= 0) {
                $calculatedBasic = 0.00;
            } elseif (isset($item['total_earned_salary']) && ((float) $item['total_earned_salary'] > 0 || $dailyRate > 0 || $basicSalarySetting > 0 || $hourlyRate > 0)) {
                $calculatedBasic = round((float) $item['total_earned_salary'], 2);
            } elseif ($salaryType === 'daily') {
                $calculatedBasic = round($workDays * $dailyRate, 2);
            } elseif ($salaryType === 'hourly' || $hourlyRate > 0) {
                $rateToUse = $hourlyRate > 0 ? $hourlyRate : ($dailyRate > 0 ? round($dailyRate / 10.0, 2) : ($basicSalarySetting > 0 ? round($basicSalarySetting / 10.0, 2) : 0));
                $calculatedBasic = round($totalHours * $rateToUse, 2);
            } else {
                $calculatedBasic = $workDays > 0 ? round(($workDays / 26.0) * $basicSalarySetting, 2) : 0.00;
            }

            // No overtime pay
            $overtimePay = 0.00;

            // Find existing salary record if any
            $existing = HrSalary::where('emp_code', $code)
                ->where('salary_month', $salaryMonth)
                ->first();

            $openingBalance = (float) ($setting->opening_balance ?? 0.00);
            $existingOpening = ($existing && $existing->opening_balance !== null && (float) $existing->opening_balance > 0)
                ? (float) $existing->opening_balance
                : $openingBalance;

            $bonus = $existing ? (float) $existing->bonus : 0.00;
            $deductions = $existing ? (float) $existing->deductions : 0.00;
            $advanceTaken = $existing ? (float) $existing->advance_taken : 0.00;
            $existingAllowance = $existing && $existing->allowance > 0 ? (float) $existing->allowance : ($workDays > 0 ? $allowance : 0.00);
            $existingBasic = ($existing && in_array($existing->status ?? '', ['paid', 'approved']))
                ? (float) $existing->basic_salary
                : $calculatedBasic;

            // Net Salary = Opening Balance + Basic + Overtime + Allowance + Bonus - Deductions - Advance
            $netSalary = max(0, round($existingOpening + $existingBasic + $overtimePay + $existingAllowance + $bonus - $deductions - $advanceTaken, 2));

            // Create or update HrSalary
            $salary = HrSalary::updateOrCreate(
                [
                    'zk_employee_id' => $emp->id,
                    'salary_month' => $salaryMonth,
                ],
                [
                    'emp_code' => $code,
                    'salary_type' => $salaryType,
                    'daily_working_hours' => $dailyWorkingHours,
                    'total_work_days' => $workDays,
                    'total_hours_worked' => $totalHours,
                    'expected_hours' => $expectedHours,
                    'overtime_hours' => $overtimeHours,
                    'daily_rate' => $dailyRate,
                    'hourly_rate' => $hourlyRate,
                    'basic_salary' => $existingBasic,
                    'opening_balance' => $existingOpening,
                    'overtime_pay' => $overtimePay,
                    'bonus' => $bonus,
                    'allowance' => $existingAllowance,
                    'deductions' => $deductions,
                    'advance_taken' => $advanceTaken,
                    'net_salary' => $netSalary,
                ]
            );

            // Update balance and payment status based on recorded payments
            $salary->recalculateBalance();

            $salaryList->push($salary->load(['employee.department', 'payments']));
        }

        return $salaryList;
    }

    /**
     * Update employee opening balance (old salary balance) and re-sync
     */
    public function updateOpeningBalance(string $empCode, float $amount, ?string $yearMonth = null): HrSalary
    {
        $yearMonth = $yearMonth ?: Carbon::now()->format('Y-m');
        $salaryMonth = Carbon::parse($yearMonth.'-01')->toDateString();

        // 1. Update in HrEmployeeSetting
        HrEmployeeSetting::where('emp_code', $empCode)->update(['opening_balance' => $amount]);

        // 2. Update in current month HrSalary
        $salary = HrSalary::where('emp_code', $empCode)
            ->where('salary_month', $salaryMonth)
            ->first();

        if ($salary) {
            $salary->opening_balance = $amount;
            $salary->net_salary = max(0, round(
                (float) $salary->opening_balance
                + (float) $salary->basic_salary
                + (float) $salary->allowance
                + (float) $salary->bonus
                - (float) $salary->deductions
                - (float) $salary->advance_taken,
                2
            ));
            $salary->save();
            $salary->recalculateBalance();
        } else {
            $this->syncMonthlySalaries($yearMonth);
            $salary = HrSalary::where('emp_code', $empCode)
                ->where('salary_month', $salaryMonth)
                ->first();
        }

        return $salary;
    }

    /**
     * Record a salary payment.
     */
    public function recordPayment(int $hrSalaryId, array $data): HrSalaryPayment
    {
        $salary = HrSalary::findOrFail($hrSalaryId);

        $payment = HrSalaryPayment::create([
            'hr_salary_id' => $salary->id,
            'emp_code' => $salary->emp_code,
            'payment_date' => $data['payment_date'] ?? Carbon::now()->toDateString(),
            'amount' => (float) $data['amount'],
            'payment_method' => $data['payment_method'] ?? 'Cash',
            'reference_no' => $data['reference_no'] ?? null,
            'notes' => $data['notes'] ?? null,
            'created_by' => auth()->id() ?? null,
        ]);

        $salary->recalculateBalance();

        return $payment;
    }

    /**
     * Create a complete backup of HR attendance and salary tables (JSON and SQL format).
     */
    public function createHrBackup(): array
    {
        $backupDir = storage_path('app/backups');
        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $timestamp = Carbon::now()->format('Y_m_d_His');
        $jsonFileName = "hr_backup_{$timestamp}.json";
        $sqlFileName = "hr_backup_{$timestamp}.sql";
        $jsonPath = "{$backupDir}/{$jsonFileName}";
        $sqlPath = "{$backupDir}/{$sqlFileName}";

        $tables = [
            'iclock_transaction',
            'hr_salaries',
            'hr_salary_payments',
            'hr_employee_settings',
        ];

        if (Schema::hasTable('attendances')) {
            $tables[] = 'attendances';
        }
        if (Schema::hasTable('salaries')) {
            $tables[] = 'salaries';
        }

        $data = [
            'timestamp' => Carbon::now()->toIso8601String(),
            'tables' => [],
        ];

        $sqlContent = "-- HR Attendance & Salary Backup generated at ".Carbon::now()->toDateTimeString()."\n";
        $sqlContent .= "-- Jaffna Gold POS / Systems\n\n";

        $counts = [];

        foreach ($tables as $table) {
            $records = DB::table($table)->get()->map(fn ($r) => (array) $r)->toArray();
            $data['tables'][$table] = $records;
            $counts[$table] = count($records);

            $sqlContent .= "-- Table: {$table} (".count($records)." rows)\n";
            if (! empty($records)) {
                foreach ($records as $row) {
                    $cols = array_keys($row);
                    $escapedCols = array_map(fn ($c) => "`{$c}`", $cols);
                    $escapedVals = array_map(function ($val) {
                        if ($val === null) {
                            return 'NULL';
                        }
                        return "'".addslashes((string) $val)."'";
                    }, array_values($row));

                    $sqlContent .= "INSERT INTO `{$table}` (".implode(', ', $escapedCols).') VALUES ('.implode(', ', $escapedVals).");\n";
                }
            }
            $sqlContent .= "\n";
        }

        file_put_contents($jsonPath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        file_put_contents($sqlPath, $sqlContent);

        return [
            'timestamp' => Carbon::now()->toDateTimeString(),
            'json_file' => $jsonPath,
            'sql_file' => $sqlPath,
            'counts' => $counts,
        ];
    }

    /**
     * Safely reset attendance and salary records with an automatic backup first.
     * Preserves employee master profiles and configured salary rates.
     */
    public function resetAttendanceAndSalary(string $scope = 'all', ?string $month = null, bool $clearOpeningBalances = true): array
    {
        // 1. Mandatory safety backup first
        $backup = $this->createHrBackup();

        $deletedCounts = [
            'iclock_transaction' => 0,
            'hr_salaries' => 0,
            'hr_salary_payments' => 0,
        ];

        DB::transaction(function () use ($scope, $month, $clearOpeningBalances, &$deletedCounts) {
            if ($clearOpeningBalances) {
                DB::table('hr_employee_settings')->update(['opening_balance' => 0.00]);
            }

            // Collect payments to be deleted so we can clean up linked StaffExpense / Expense records
            if ($scope === 'month' && ! empty($month)) {
                $monthDate = Carbon::parse($month.'-01');
                $startDate = $monthDate->copy()->startOfMonth()->toDateString();
                $endDate = $monthDate->copy()->endOfMonth()->toDateString();
                $paymentsToDelete = DB::table('hr_salary_payments')
                    ->whereBetween('payment_date', [$startDate, $endDate])
                    ->get();
            } else {
                $paymentsToDelete = DB::table('hr_salary_payments')->get();
            }

            foreach ($paymentsToDelete as $pmt) {
                $ref = (string) ($pmt->reference_no ?? '');
                $staffExpIds = [];
                $expIds = [];

                if (preg_match('/STAFF-EXP-(\d+)/', $ref, $m)) {
                    $staffExpIds[] = (int) $m[1];
                }
                if (preg_match('/(?:^|\|\s*)EXP-(\d+)/', $ref, $m)) {
                    $expIds[] = (int) $m[1];
                }

                // Restore cash in hand & today's POS session if it was a cash expense
                if ((! empty($staffExpIds) || ! empty($expIds)) && strcasecmp((string) ($pmt->payment_method ?? 'Cash'), 'Cash') === 0) {
                    $amount = (float) ($pmt->amount ?? 0);
                    if ($amount > 0) {
                        $cashInHandRecord = DB::table('cash_in_hands')->where('key', 'cash_amount')->first();
                        if ($cashInHandRecord) {
                            DB::table('cash_in_hands')
                                ->where('key', 'cash_amount')
                                ->update([
                                    'value' => $cashInHandRecord->value + $amount,
                                    'updated_at' => now(),
                                ]);
                        }

                        if (! empty($pmt->payment_date) && Carbon::parse($pmt->payment_date)->isToday()) {
                            $uid = $pmt->created_by ?: 1;
                            $session = \App\Models\POSSession::getTodaySession($uid);
                            if ($session) {
                                $session->expenses = max(0, ($session->expenses ?? 0) - $amount);
                                $session->save();
                                $session->calculateDifference();
                            }
                        }
                    }
                }

                if (! empty($staffExpIds)) {
                    DB::table('staff_expenses')->whereIn('id', $staffExpIds)->delete();
                }
                if (! empty($expIds)) {
                    DB::table('expenses')->whereIn('id', $expIds)->delete();
                }
            }

            if ($scope === 'month' && ! empty($month)) {
                $monthDate = Carbon::parse($month.'-01');
                $start = $monthDate->copy()->startOfMonth()->toDateTimeString();
                $end = $monthDate->copy()->endOfMonth()->toDateTimeString();
                $salaryMonth = $monthDate->toDateString();
                $startDate = $monthDate->copy()->startOfMonth()->toDateString();
                $endDate = $monthDate->copy()->endOfMonth()->toDateString();

                // Delete payments in this month
                $deletedCounts['hr_salary_payments'] = DB::table('hr_salary_payments')
                    ->whereBetween('payment_date', [$startDate, $endDate])
                    ->delete();

                // Delete monthly salaries for this month
                $deletedCounts['hr_salaries'] = DB::table('hr_salaries')
                    ->where('salary_month', $salaryMonth)
                    ->delete();

                // Delete attendance punch logs for this month
                $deletedCounts['iclock_transaction'] = DB::table('iclock_transaction')
                    ->whereBetween('punch_time', [$start, $end])
                    ->delete();
            } else {
                // Scope: 'all'
                $deletedCounts['hr_salary_payments'] = DB::table('hr_salary_payments')->delete();
                $deletedCounts['hr_salaries'] = DB::table('hr_salaries')->delete();
                $deletedCounts['iclock_transaction'] = DB::table('iclock_transaction')->delete();

                if (Schema::hasTable('attendances')) {
                    DB::table('attendances')->delete();
                }
                if (Schema::hasTable('salaries')) {
                    DB::table('salaries')->delete();
                }
            }
        });

        // Re-sync monthly salary to show clean 0-hours state
        $syncMonth = $month ?: Carbon::now()->format('Y-m');
        $this->syncMonthlySalaries($syncMonth);

        return [
            'success' => true,
            'scope' => $scope,
            'month' => $month,
            'backup' => $backup,
            'deleted' => $deletedCounts,
            'message' => 'Attendance punch logs and salary records have been reset successfully.',
        ];
    }
}
