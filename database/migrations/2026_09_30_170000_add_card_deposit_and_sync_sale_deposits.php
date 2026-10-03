<?php

use App\Models\SavingMethod;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure card_deposit saving method exists
        SavingMethod::firstOrCreate(
            ['code' => 'card_deposit'],
            [
                'name' => 'Card / POS Deposit',
                'description' => 'Direct Card / Online POS Payment to Bank Account',
                'icon' => 'bi-credit-card-2-front',
                'color' => 'primary',
                'is_active' => true,
            ]
        );

        // 2. Sync existing bank_transfer and online/card sales into deposits table
        $electronicPayments = DB::table('payments')
            ->join('sales', 'payments.sale_id', '=', 'sales.id')
            ->whereIn('payments.payment_method', ['bank_transfer', 'online', 'card'])
            ->where('payments.amount', '>', 0)
            ->select([
                'payments.id as payment_id',
                'payments.payment_method',
                'payments.amount',
                'payments.bank_name',
                'payments.payment_date',
                'payments.created_at as payment_created_at',
                'sales.id as sale_id',
                'sales.invoice_number',
                'sales.user_id as sale_user_id',
                'sales.created_at as sale_created_at',
            ])
            ->get();

        $inserts = [];
        $existingRefs = DB::table('deposits')
            ->whereIn('source_of_funds', ['sale_bank_transfer', 'sale_card'])
            ->pluck('reference_no')
            ->flip()
            ->all();

        foreach ($electronicPayments as $pay) {
            $ref = $pay->invoice_number ?: ('PAY-'.$pay->payment_id);
            if (isset($existingRefs[$ref])) {
                continue;
            }

            $isCard = in_array(strtolower($pay->payment_method), ['online', 'card']);
            $savingMethod = $isCard ? 'card_deposit' : 'bank_deposit';
            $sourceOfFunds = $isCard ? 'sale_card' : 'sale_bank_transfer';
            $methodTitle = $isCard ? 'Card / Online Payment' : 'Direct Bank Transfer';
            $bankSuffix = ! empty($pay->bank_name) ? " ({$pay->bank_name})" : '';

            $date = $pay->payment_date
                ? Carbon::parse($pay->payment_date)->toDateString()
                : ($pay->sale_created_at ? Carbon::parse($pay->sale_created_at)->toDateString() : now()->toDateString());

            $createdAt = $pay->payment_created_at ?? $pay->sale_created_at ?? now();

            $inserts[] = [
                'date' => $date,
                'amount' => $pay->amount,
                'saving_method' => $savingMethod,
                'source_of_funds' => $sourceOfFunds,
                'reference_no' => $ref,
                'user_id' => $pay->sale_user_id ?? 1,
                'status' => 'completed',
                'description' => "{$methodTitle} for Sale #{$ref}{$bankSuffix}",
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];
            $existingRefs[$ref] = true;
        }

        if (! empty($inserts)) {
            foreach (array_chunk($inserts, 500) as $chunk) {
                DB::table('deposits')->insert($chunk);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('deposits')
            ->whereIn('source_of_funds', ['sale_bank_transfer', 'sale_card'])
            ->delete();

        SavingMethod::where('code', 'card_deposit')->delete();
    }
};
