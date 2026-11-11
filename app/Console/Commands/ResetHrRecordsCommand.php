<?php

namespace App\Console\Commands;

use App\Services\HrService;
use Illuminate\Console\Command;

class ResetHrRecordsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hr:reset-records 
                            {--scope=all : Reset scope: "all" or "month"} 
                            {--month= : Month to reset in YYYY-MM format (required if scope is month)} 
                            {--force : Force reset without confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely reset biometric attendance punches, monthly salaries, and salary payment logs with an automated backup first, preserving staff profiles.';

    /**
     * Execute the console command.
     */
    public function handle(HrService $hrService): int
    {
        $scope = $this->option('scope') ?: 'all';
        $month = $this->option('month');

        if ($scope === 'month' && empty($month)) {
            $this->error('Error: You must provide --month=YYYY-MM when scope is "month".');
            return Command::FAILURE;
        }

        $this->warn('====================================================');
        $this->warn('  JAFFNA GOLD - HR ATTENDANCE & SALARY RESET WIZARD  ');
        $this->warn('====================================================');
        $this->line("Scope: <info>{$scope}</info>" . ($month ? " (Month: <info>{$month}</info>)" : ''));
        $this->line('Tables to reset:');
        $this->line('  - <comment>iclock_transaction</comment> (Biometric punch logs)');
        $this->line('  - <comment>hr_salaries</comment> (Monthly salary records)');
        $this->line('  - <comment>hr_salary_payments</comment> (Salary payment transactions)');
        $this->line('');
        $this->info('NOTE: Staff details, employee names, and salary rates will NOT be deleted.');
        $this->info('An automatic SQL and JSON backup will be created in storage/app/backups/ before deletion.');
        $this->warn('====================================================');

        if (! $this->option('force')) {
            if (! $this->confirm('Are you absolutely sure you want to proceed with resetting these records?', false)) {
                $this->info('Reset cancelled. No data was modified.');
                return Command::SUCCESS;
            }
        }

        $this->info('Creating automatic safety backup...');
        $result = $hrService->resetAttendanceAndSalary($scope, $month);

        $this->info('Backup created:');
        $this->line('  JSON: ' . $result['backup']['json_file']);
        $this->line('  SQL:  ' . $result['backup']['sql_file']);
        $this->line('  Backed up counts: ' . json_encode($result['backup']['counts']));

        $this->newLine();
        $this->info('Reset completed successfully!');
        $this->line('  Punches deleted:  ' . ($result['deleted']['iclock_transaction'] ?? 0));
        $this->line('  Salaries deleted: ' . ($result['deleted']['hr_salaries'] ?? 0));
        $this->line('  Payments deleted: ' . ($result['deleted']['hr_salary_payments'] ?? 0));

        return Command::SUCCESS;
    }
}
