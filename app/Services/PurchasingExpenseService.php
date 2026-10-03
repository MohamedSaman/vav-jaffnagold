<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\HandmadeSupplier;
use App\Models\HandmadeSupplierPayment;
use App\Models\POSSession;
use App\Models\StaffExpense;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchasingExpenseService
{
    /**
     * Primary local suppliers mentioned by business requirements.
     */
    public const PRIMARY_SUPPLIERS = [
        'Mano anna' => ['mano'],
        'RAMESH ANNA' => ['ramesh'],
        'Sutha anna' => ['sutha', 'suthu'],
    ];

    /**
     * Common purchasing expense purpose categories.
     */
    public const PURPOSE_CATEGORIES = [
        'Raw Gold / Poun Purchase',
        'Advance Payment (Kudutha Kasu)',
        'Order Balance Settlement',
        'Handmade Crafting / Repair',
        'Workshop / Plating Fee',
        'Stones & Setting',
        'General Supplier Payout',
    ];

    /**
     * Check if a given expense type and description indicate a purchasing / supplier expense.
     */
    public static function isPurchasingExpense(?string $type, ?string $description = null): bool
    {
        $text = strtolower(trim(($type ?? '').' '.($description ?? '')));
        if (empty($text)) {
            return false;
        }

        // Check primary suppliers
        foreach (self::PRIMARY_SUPPLIERS as $name => $keywords) {
            foreach ($keywords as $k) {
                if (str_contains($text, $k)) {
                    return true;
                }
            }
        }

        // Check keywords
        if (str_contains($text, 'purchas') || str_contains($text, 'supplier')) {
            return true;
        }

        // Check all active handmade suppliers
        $suppliers = HandmadeSupplier::where('is_active', true)->pluck('name');
        foreach ($suppliers as $sName) {
            if (! empty($sName) && str_contains($text, strtolower(trim($sName)))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalize or detect the supplier name from type or description.
     */
    public static function detectSupplierName(?string $type, ?string $description = null): string
    {
        $text = strtolower(trim(($type ?? '').' '.($description ?? '')));

        if (str_contains($text, 'mano')) {
            return 'Mano anna';
        }
        if (str_contains($text, 'ramesh')) {
            return 'RAMESH ANNA';
        }
        if (str_contains($text, 'sutha') || str_contains($text, 'suthu')) {
            return 'Sutha anna';
        }

        // Check registered handmade suppliers
        $suppliers = HandmadeSupplier::where('is_active', true)->get();
        foreach ($suppliers as $supplier) {
            if (! empty($supplier->name) && str_contains($text, strtolower(trim($supplier->name)))) {
                return $supplier->name;
            }
        }

        return ! empty($type) ? trim($type) : 'Supplier Payout';
    }

    /**
     * Build an Eloquent query for purchasing expenses in StaffExpense.
     */
    public static function getPurchasingExpensesQuery(): Builder
    {
        $keywords = ['mano', 'ramesh', 'sutha', 'suthu', 'purchas', 'supplier'];
        $handmadeNames = HandmadeSupplier::pluck('name')->toArray();
        foreach ($handmadeNames as $name) {
            $n = strtolower(trim($name));
            if (! empty($n) && ! in_array($n, $keywords)) {
                $keywords[] = $n;
            }
        }

        return StaffExpense::where(function ($query) use ($keywords) {
            foreach ($keywords as $k) {
                $query->orWhere('expense_type', 'LIKE', "%{$k}%")
                    ->orWhere('description', 'LIKE', "%{$k}%");
            }
        });
    }

    /**
     * Find matching HandmadeSupplier model by supplier name or keyword.
     */
    public static function findHandmadeSupplier(string $supplierName): ?HandmadeSupplier
    {
        $text = strtolower(trim($supplierName));
        if (str_contains($text, 'mano')) {
            return HandmadeSupplier::where('name', 'LIKE', '%mano%')->first();
        }
        if (str_contains($text, 'ramesh')) {
            return HandmadeSupplier::where('name', 'LIKE', '%ramesh%')->first();
        }
        if (str_contains($text, 'sutha') || str_contains($text, 'suthu')) {
            return HandmadeSupplier::where('name', 'LIKE', '%sutha%')->orWhere('name', 'LIKE', '%suthu%')->first();
        }

        return HandmadeSupplier::where('name', 'LIKE', "%{$supplierName}%")->first();
    }

    /**
     * Record a new purchasing expense.
     * Handles: StaffExpense, Expense, cash_in_hands deduction, POSSession update, and HandmadeSupplierPayment sync.
     */
    public static function createPurchasingExpense(array $data): StaffExpense
    {
        return DB::transaction(function () use ($data) {
            $supplierName = trim($data['supplier_name']);
            $amount = (float) $data['amount'];
            $expenseDate = $data['expense_date'] ?? date('Y-m-d');
            $purpose = $data['purpose'] ?? 'General Supplier Payout';
            $notes = $data['description'] ?? '';
            $staffId = $data['staff_id'] ?? Auth::id();

            $fullDescription = trim("{$purpose}".($notes ? " - {$notes}" : ''));

            // Ensure category exists
            ExpenseCategory::firstOrCreate(
                ['expense_category' => $supplierName],
                ['type' => 'daily']
            );
            ExpenseCategory::firstOrCreate(
                ['expense_category' => 'Purchasing Expenses'],
                ['type' => 'daily']
            );

            // 1. Create StaffExpense
            $staffExpense = StaffExpense::create([
                'staff_id' => $staffId,
                'expense_type' => $supplierName,
                'amount' => $amount,
                'description' => $fullDescription,
                'expense_date' => $expenseDate,
                'status' => 'approved',
                'admin_notes' => 'Auto-approved Purchasing Expense (Shop Staff)',
            ]);

            // 2. Create Regular Expense
            $userName = Auth::user()->name ?? 'Shop Staff';
            Expense::create([
                'category' => 'Purchasing Expense - '.$supplierName,
                'amount' => $amount,
                'description' => "Staff: {$userName} | Supplier: {$supplierName} - {$fullDescription}",
                'date' => $expenseDate,
                'expense_type' => 'daily',
                'user_id' => $staffId,
            ]);

            // 3. Deduct from cash_in_hands
            $cashInHandRecord = DB::table('cash_in_hands')->where('key', 'cash_amount')->first();
            if ($cashInHandRecord) {
                DB::table('cash_in_hands')
                    ->where('key', 'cash_amount')
                    ->update([
                        'value' => $cashInHandRecord->value - $amount,
                        'updated_at' => now(),
                    ]);
            }

            // 4. Update POS session if today
            try {
                if (Carbon::parse($expenseDate)->isToday()) {
                    $session = POSSession::getTodaySession($staffId);
                    if ($session) {
                        $session->expenses = ($session->expenses ?? 0) + $amount;
                        $session->save();
                        $session->calculateDifference();
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to update POS session for purchasing expense: '.$e->getMessage());
            }

            // 5. Sync to HandmadeSupplierPayment if supplier exists
            try {
                $supplierModel = self::findHandmadeSupplier($supplierName);
                if ($supplierModel) {
                    HandmadeSupplierPayment::create([
                        'supplier_id' => $supplierModel->id,
                        'user_id' => $staffId,
                        'amount' => $amount,
                        'payment_method' => 'cash',
                        'payment_date' => $expenseDate,
                        'notes' => "Purchasing Expense: {$fullDescription}",
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to sync HandmadeSupplierPayment: '.$e->getMessage());
            }

            return $staffExpense;
        });
    }

    /**
     * Delete a purchasing expense and restore cash in hand.
     */
    public static function deletePurchasingExpense(int $expenseId, ?int $staffId = null): bool
    {
        return DB::transaction(function () use ($expenseId, $staffId) {
            $query = StaffExpense::where('id', $expenseId);
            if ($staffId !== null) {
                $query->where('staff_id', $staffId);
            }
            $expense = $query->first();

            if (! $expense) {
                return false;
            }

            // If approved, restore cash in hand
            if ($expense->status === 'approved') {
                $cashInHandRecord = DB::table('cash_in_hands')->where('key', 'cash_amount')->first();
                if ($cashInHandRecord) {
                    DB::table('cash_in_hands')
                        ->where('key', 'cash_amount')
                        ->update([
                            'value' => $cashInHandRecord->value + $expense->amount,
                            'updated_at' => now(),
                        ]);
                }
            }

            // Delete linked Expense record matching description or date
            Expense::where('amount', $expense->amount)
                ->where('date', $expense->expense_date->toDateString())
                ->where('category', 'LIKE', "%{$expense->expense_type}%")
                ->delete();

            $expense->delete();

            return true;
        });
    }
}
