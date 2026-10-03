<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('custom_product_inquiries', function (Blueprint $table) {
            $table->decimal('quoted_price', 12, 2)->nullable()->after('estimated_price');
            $table->text('admin_notes')->nullable()->after('customer_notes');
            $table->foreignId('assigned_to')->nullable()->after('status')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_product_inquiries', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropColumn(['quoted_price', 'admin_notes', 'assigned_to']);
        });
    }
};
