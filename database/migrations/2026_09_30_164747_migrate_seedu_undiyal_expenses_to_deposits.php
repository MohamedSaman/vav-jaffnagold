<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $records = \Illuminate\Support\Facades\DB::table('staff_expenses')
            ->where(function ($q) {
                $q->where('expense_type', 'LIKE', '%seedu%')
                    ->orWhere('expense_type', 'LIKE', '%undiyal%');
            })
            ->get();

        foreach ($records as $r) {
            $method = stripos($r->expense_type, 'undiyal') !== false ? 'undiyal' : 'seedu';
            \Illuminate\Support\Facades\DB::table('deposits')->insert([
                'date' => $r->expense_date,
                'amount' => $r->amount,
                'saving_method' => $method,
                'source_of_funds' => 'cash_sale',
                'reference_no' => $r->expense_type,
                'user_id' => $r->staff_id,
                'status' => 'completed',
                'description' => $r->description ?: $r->expense_type,
                'created_at' => $r->created_at ?: now(),
                'updated_at' => $r->updated_at ?: now(),
            ]);
        }

        // Delete from staff_expenses so they do not pollute operating expenses
        \Illuminate\Support\Facades\DB::table('staff_expenses')
            ->where(function ($q) {
                $q->where('expense_type', 'LIKE', '%seedu%')
                    ->orWhere('expense_type', 'LIKE', '%undiyal%');
            })
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversible if needed
    }
};
