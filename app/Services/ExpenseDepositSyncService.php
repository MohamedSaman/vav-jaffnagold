<?php

namespace App\Services;

use App\Models\Deposit;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ExpenseDepositSyncService
{
    /**
     * Determine if an expense category or description represents a deposit / saving (Seedu, Undiyal, Bank Deposit, etc.)
     */
    public static function isDeposit(?string $category, ?string $description = null): bool
    {
        $text = trim(($category ?? '').' '.($description ?? ''));
        if (empty($text)) {
            return false;
        }

        return (bool) preg_match('/\b(seedu|sittu|sitdu|seettu|settu|chit|undiyal|unndiyal|untiyal|unttyal|undeyal|undaiyal|unddiyal|undiuyal|undiyai|undiyel|unnduyal|unnndiya|deposit|doposit|saving|சீட்டு|உண்டியல்)\b/i', $text);
    }

    /**
     * Determine saving method code from category/description
     */
    public static function detectSavingMethod(?string $category, ?string $description = null): string
    {
        $text = strtolower(trim(($category ?? '').' '.($description ?? '')));

        if (preg_match('/\b(undiyal|unndiyal|untiyal|unttyal|undeyal|undaiyal|unddiyal|undiuyal|undiyai|undiyel|unnduyal|unnndiya|உண்டியல்)\b/i', $text)) {
            return 'undiyal';
        }

        if (preg_match('/card|pos/i', $text)) {
            return 'card_deposit';
        }

        if (preg_match('/bank/i', $text)) {
            return 'bank_deposit';
        }

        return 'seedu';
    }

    /**
     * Sync or create a Deposit record from an Expense
     */
    public static function syncExpenseToDeposit(Expense $expense): ?Deposit
    {
        try {
            if (! self::isDeposit($expense->category, $expense->description)) {
                return null;
            }

            $method = self::detectSavingMethod($expense->category, $expense->description);
            $refNo = 'EXP-'.$expense->id;
            $date = $expense->date ? Carbon::parse($expense->date)->format('Y-m-d') : Carbon::now()->format('Y-m-d');

            // Clean category title for readable description
            $cleanTitle = trim(str_ireplace(['Staff Expense -', 'Staff Expense', 'Staff:'], '', $expense->category ?: ''));
            if (empty($cleanTitle)) {
                $cleanTitle = $expense->description ?: ucfirst($method);
            }

            $userId = $expense->user_id ?: \Illuminate\Support\Facades\Auth::id();
            if ($userId && ! \App\Models\User::where('id', $userId)->exists()) {
                $userId = \App\Models\User::value('id');
            }

            return Deposit::updateOrCreate(
                ['reference_no' => $refNo],
                [
                    'date' => $date,
                    'amount' => $expense->amount,
                    'saving_method' => $method,
                    'source_of_funds' => 'cash_sale',
                    'description' => 'From Expense: '.$cleanTitle,
                    'user_id' => $userId,
                    'status' => 'completed',
                ]
            );
        } catch (\Throwable $e) {
            Log::error("Failed to sync expense #{$expense->id} to deposits: ".$e->getMessage());

            return null;
        }
    }

    /**
     * Delete linked deposit when an expense is deleted
     */
    public static function deleteDepositForExpense(Expense $expense): void
    {
        try {
            Deposit::where('reference_no', 'EXP-'.$expense->id)->delete();
        } catch (\Throwable $e) {
            Log::error("Failed to delete linked deposit for expense #{$expense->id}: ".$e->getMessage());
        }
    }

    /**
     * Sync all existing expenses that match savings/deposits into deposits table
     */
    public static function syncAllExistingExpenses(): int
    {
        $expenses = Expense::all();
        $syncedCount = 0;

        foreach ($expenses as $expense) {
            if (self::isDeposit($expense->category, $expense->description)) {
                if (self::syncExpenseToDeposit($expense)) {
                    $syncedCount++;
                }
            }
        }

        return $syncedCount;
    }
}
