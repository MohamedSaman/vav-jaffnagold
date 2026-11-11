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
        'MANO ANNA' => ['mano anna', 'mano'],
        'KANIY MANO' => ['kaniy', 'kolaya'],
        'RAMESH ANNA' => ['ramesh'],
        'SUTHA ANNA' => ['sutha', 'suthu'],
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

        if (str_contains($text, 'kaniy') || str_contains($text, 'kolaya')) {
            return 'KANIY MANO';
        }
        if (str_contains($text, 'mano')) {
            return 'MANO ANNA';
        }
        if (str_contains($text, 'kombu') || str_contains($text, 'mullai') || str_contains($text, 'maran')) {
            return 'MARAN THALAI';
        }
        if (str_contains($text, 'prahas') || str_contains($text, 'pragas') || str_contains($text, 'prakas') || str_contains($text, 'pirakas')) {
            return 'PRAKAS';
        }
        if (str_contains($text, 'sathes dad')) {
            return 'SATHES DAD';
        }
        if (str_contains($text, 'sathes anna')) {
            return 'SATHES ANNA';
        }
        if (str_contains($text, 'mothiram') || str_contains($text, 'mothgiram') || str_contains($text, 'mothiran') || str_contains($text, 'uthayan') || str_contains($text, 'udayan') || (str_contains($text, 'ring') && !str_contains($text, 'spring'))) {
            return 'UDAYAN RING';
        }
        if (str_contains($text, 'sanger') || str_contains($text, 'sankar') || str_contains($text, 'sangar')) {
            return 'SANKAR ANNA';
        }
        if (str_contains($text, 'sutha') || str_contains($text, 'suthu') || str_contains($text, 'sudatha')) {
            return 'SUTHA ANNA';
        }
        if (str_contains($text, 'athaan') || str_contains($text, 'aththaan') || str_contains($text, 'thanga thanniy')) {
            return 'ATHAAN THANGA THANNIY';
        }
        if (str_contains($text, 'ramesh')) {
            return 'RAMESH ANNA';
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
     * Get search keywords and aliases for a supplier.
     */
    public static function getSupplierKeywords(string $supplierName): array
    {
        $name = strtoupper(trim($supplierName));

        if (str_contains($name, 'KANIY') || str_contains($name, 'KOLAYA')) {
            return ['kaniy mano', 'kaniy', 'kolaya'];
        }
        if (str_contains($name, 'MANO')) {
            return ['mano anna', 'mano'];
        }
        if (str_contains($name, 'MARAN') || str_contains($name, 'KOMBU') || str_contains($name, 'MULLAI')) {
            return ['maran', 'kombu', 'mullai'];
        }
        if (str_contains($name, 'PRAKAS') || str_contains($name, 'PRAHAS') || str_contains($name, 'PRAGAS') || str_contains($name, 'PIRAKAS')) {
            return ['prakas', 'prahas', 'pragas', 'pirakas'];
        }
        if (str_contains($name, 'SATHES DAD')) {
            return ['sathes dad'];
        }
        if (str_contains($name, 'SATHES ANNA')) {
            return ['sathes anna'];
        }
        if (str_contains($name, 'UDAYAN') || str_contains($name, 'UTHAYAN') || str_contains($name, 'RING') || str_contains($name, 'MOTHIRAM')) {
            return ['udayan', 'uthayan', 'mothiram', 'mothgiram', 'mothiran', 'ring'];
        }
        if (str_contains($name, 'SANKAR') || str_contains($name, 'SANGER') || str_contains($name, 'SANGAR')) {
            return ['sankar', 'sanger', 'sangar'];
        }
        if (str_contains($name, 'SUTHA') || str_contains($name, 'SUTHU') || str_contains($name, 'SUDATHA')) {
            return ['sutha', 'suthu', 'sudatha'];
        }
        if (str_contains($name, 'ATHAAN') || str_contains($name, 'ATHTHAAN')) {
            return ['athaan', 'aththaan', 'thanga thanniy'];
        }
        if (str_contains($name, 'RAMESH')) {
            return ['ramesh'];
        }

        return [strtolower($supplierName)];
    }

    /**
     * Get color styling and icons for a supplier.
     */
    public static function getSupplierColor(string $supplierName): array
    {
        $name = strtoupper(trim($supplierName));

        if (str_contains($name, 'KANIY') || str_contains($name, 'KOLAYA')) {
            return [
                'bg' => '#0891b2', // Cyan
                'light_bg' => '#ecfeff',
                'border' => '#a5f3fc',
                'text' => '#0e7490',
                'icon' => 'bi-gem',
                'badge' => 'info',
            ];
        }
        if (str_contains($name, 'MANO')) {
            return [
                'bg' => '#2563eb', // Royal Blue
                'light_bg' => '#eff6ff',
                'border' => '#bfdbfe',
                'text' => '#1d4ed8',
                'icon' => 'bi-person-fill',
                'badge' => 'primary',
            ];
        }
        if (str_contains($name, 'MARAN') || str_contains($name, 'KOMBU') || str_contains($name, 'MULLAI')) {
            return [
                'bg' => '#ea580c', // Orange
                'light_bg' => '#fff7ed',
                'border' => '#fed7aa',
                'text' => '#c2410c',
                'icon' => 'bi-gem',
                'badge' => 'warning',
            ];
        }
        if (str_contains($name, 'PRAKAS') || str_contains($name, 'PRAHAS') || str_contains($name, 'PRAGAS') || str_contains($name, 'PIRAKAS')) {
            return [
                'bg' => '#7c3aed', // Purple
                'light_bg' => '#f5f3ff',
                'border' => '#ddd6fe',
                'text' => '#6d28d9',
                'icon' => 'bi-person-badge',
                'badge' => 'purple',
            ];
        }
        if (str_contains($name, 'SATHES DAD')) {
            return [
                'bg' => '#059669', // Emerald green
                'light_bg' => '#ecfdf5',
                'border' => '#a7f3d0',
                'text' => '#047857',
                'icon' => 'bi-person-heart',
                'badge' => 'success',
            ];
        }
        if (str_contains($name, 'SATHES ANNA')) {
            return [
                'bg' => '#0284c7', // Sky Blue
                'light_bg' => '#f0f9ff',
                'border' => '#bae6fd',
                'text' => '#0369a1',
                'icon' => 'bi-person-check',
                'badge' => 'info',
            ];
        }
        if (str_contains($name, 'UDAYAN') || str_contains($name, 'UTHAYAN') || str_contains($name, 'RING') || str_contains($name, 'MOTHIRAM')) {
            return [
                'bg' => '#e11d48', // Crimson Rose
                'light_bg' => '#fff1f2',
                'border' => '#fecdd3',
                'text' => '#be123c',
                'icon' => 'bi-circle',
                'badge' => 'danger',
            ];
        }
        if (str_contains($name, 'SANKAR') || str_contains($name, 'SANGER') || str_contains($name, 'SANGAR')) {
            return [
                'bg' => '#4f46e5', // Indigo
                'light_bg' => '#eef2ff',
                'border' => '#c7d2fe',
                'text' => '#3730a3',
                'icon' => 'bi-hammer',
                'badge' => 'indigo',
            ];
        }
        if (str_contains($name, 'SUTHA') || str_contains($name, 'SUTHU') || str_contains($name, 'SUDATHA')) {
            return [
                'bg' => '#0d9488', // Teal
                'light_bg' => '#f0fdfa',
                'border' => '#99f6e4',
                'text' => '#0f766e',
                'icon' => 'bi-shield-shaded',
                'badge' => 'teal',
            ];
        }
        if (str_contains($name, 'ATHAAN') || str_contains($name, 'ATHTHAAN')) {
            return [
                'bg' => '#d97706', // Amber Gold
                'light_bg' => '#fffbeb',
                'border' => '#fde68a',
                'text' => '#b45309',
                'icon' => 'bi-droplet-half',
                'badge' => 'warning',
            ];
        }
        if (str_contains($name, 'RAMESH')) {
            return [
                'bg' => '#6366f1', // Royal Violet
                'light_bg' => '#e0e7ff',
                'border' => '#c7d2fe',
                'text' => '#4338ca',
                'icon' => 'bi-stars',
                'badge' => 'primary',
            ];
        }

        return [
            'bg' => '#475569', // Slate
            'light_bg' => '#f8fafc',
            'border' => '#cbd5e1',
            'text' => '#334155',
            'icon' => 'bi-person',
            'badge' => 'secondary',
        ];
    }

    /**
     * Build an Eloquent query for purchasing expenses in StaffExpense.
     */
    public static function getPurchasingExpensesQuery(): Builder
    {
        $keywords = [
            'mano', 'kombu', 'mullai', 'maran', 'prakas', 'prahas', 'pragas', 'pirakas',
            'sathes', 'udayan', 'uthayan', 'mothiram', 'mothgiram', 'sankar', 'sanger', 'sangar',
            'sutha', 'suthu', 'athaan', 'aththaan', 'ramesh', 'purchas', 'supplier',
        ];
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
     * Apply supplier filter query with exclusion guards for overlapping names (e.g. MANO ANNA vs KANIY MANO).
     */
    public static function applySupplierFilter(Builder $query, string $supName): Builder
    {
        $keywords = self::getSupplierKeywords($supName);
        $upper = strtoupper(trim($supName));

        $query->where(function ($sub) use ($keywords, $supName, $upper) {
            $sub->where(function ($inner) use ($keywords, $supName) {
                $inner->where('expense_type', 'LIKE', "%{$supName}%")
                    ->orWhere('description', 'LIKE', "%{$supName}%");
                foreach ($keywords as $k) {
                    $inner->orWhere('expense_type', 'LIKE', "%{$k}%")
                        ->orWhere('description', 'LIKE', "%{$k}%");
                }
            });

            // If filtering for MANO ANNA, exclude KANIY MANO / KOLAYA to avoid overlap
            if (str_contains($upper, 'MANO ANNA') || $upper === 'MANO') {
                $sub->where(function ($notInner) {
                    $notInner->where('expense_type', 'NOT LIKE', '%kaniy%')
                        ->where('expense_type', 'NOT LIKE', '%kolaya%')
                        ->where('description', 'NOT LIKE', '%kaniy%')
                        ->where('description', 'NOT LIKE', '%kolaya%');
                });
            }
        });

        return $query;
    }

    /**
     * Find matching HandmadeSupplier model by supplier name or keyword.
     */
    public static function findHandmadeSupplier(string $supplierName): ?HandmadeSupplier
    {
        $text = strtolower(trim($supplierName));
        if (str_contains($text, 'kaniy') || str_contains($text, 'kolaya')) {
            return HandmadeSupplier::where('name', 'LIKE', '%kaniy%')->first();
        }
        if (str_contains($text, 'mano')) {
            return HandmadeSupplier::where('name', 'LIKE', '%mano anna%')->orWhere('name', 'MANO ANNA')->first()
                ?: HandmadeSupplier::where('name', 'LIKE', '%mano%')->where('name', 'NOT LIKE', '%kaniy%')->first();
        }
        if (str_contains($text, 'kombu') || str_contains($text, 'mullai') || str_contains($text, 'maran')) {
            return HandmadeSupplier::where('name', 'LIKE', '%maran%')->first();
        }
        if (str_contains($text, 'prahas') || str_contains($text, 'pragas') || str_contains($text, 'prakas') || str_contains($text, 'pirakas')) {
            return HandmadeSupplier::where('name', 'LIKE', '%prakas%')->first();
        }
        if (str_contains($text, 'sathes dad')) {
            return HandmadeSupplier::where('name', 'LIKE', '%sathes dad%')->first();
        }
        if (str_contains($text, 'sathes anna')) {
            return HandmadeSupplier::where('name', 'LIKE', '%sathes anna%')->first();
        }
        if (str_contains($text, 'mothiram') || str_contains($text, 'mothgiram') || str_contains($text, 'mothiran') || str_contains($text, 'uthayan') || str_contains($text, 'udayan')) {
            return HandmadeSupplier::where('name', 'LIKE', '%udayan%')->orWhere('name', 'LIKE', '%mothiram%')->first();
        }
        if (str_contains($text, 'sanger') || str_contains($text, 'sankar') || str_contains($text, 'sangar')) {
            return HandmadeSupplier::where('name', 'LIKE', '%sankar%')->orWhere('name', 'LIKE', '%sanger%')->first();
        }
        if (str_contains($text, 'sutha') || str_contains($text, 'suthu')) {
            return HandmadeSupplier::where('name', 'LIKE', '%sutha%')->orWhere('name', 'LIKE', '%suthu%')->first();
        }
        if (str_contains($text, 'athaan') || str_contains($text, 'aththaan')) {
            return HandmadeSupplier::where('name', 'LIKE', '%athaan%')->orWhere('name', 'LIKE', '%aththaan%')->first();
        }
        if (str_contains($text, 'ramesh')) {
            return HandmadeSupplier::where('name', 'LIKE', '%ramesh%')->first();
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
