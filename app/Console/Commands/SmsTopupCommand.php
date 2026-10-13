<?php

namespace App\Console\Commands;

use App\Models\SmsTopup;
use App\Services\SmsService;
use Illuminate\Console\Command;

class SmsTopupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:topup 
                            {amount : The topup amount in Rs. (e.g. 1000)} 
                            {--notes= : Optional description or bank reference} 
                            {--provider=0759037101 : Contact phone number of service provider}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Credit SMS balance for Jaffna Gold (Provider: 0759037101)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $amount = (float) $this->argument('amount');
        $provider = (string) $this->option('provider');
        $notes = $this->option('notes') ?: "Provider top-up credited by {$provider}";

        if ($amount <= 0) {
            $this->error('The topup amount must be greater than 0.');

            return self::FAILURE;
        }

        $topup = SmsTopup::create([
            'amount' => $amount,
            'notes' => $notes,
            'user_id' => null,
        ]);

        $newBalance = SmsService::getBalance();
        $totalTopups = SmsService::getTotalTopupAmount();
        $totalSpent = SmsService::getTotalSmsCost();

        $this->info('✅ Successfully credited Rs. '.number_format($amount, 2).' to SMS balance!');
        $this->table(
            ['Field', 'Value'],
            [
                ['Top-Up ID', $topup->id],
                ['Provider Phone', $provider],
                ['Amount Credited', 'Rs. '.number_format($amount, 2)],
                ['Notes / Reference', $notes],
                ['New Available Balance', 'Rs. '.number_format($newBalance, 2)],
                ['Total Top-Ups Credited', 'Rs. '.number_format($totalTopups, 2)],
                ['Total Cost Spent', 'Rs. '.number_format($totalSpent, 2)],
            ]
        );

        return self::SUCCESS;
    }
}
