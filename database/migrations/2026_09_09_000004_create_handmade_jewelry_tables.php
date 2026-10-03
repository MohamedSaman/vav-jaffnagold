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
        // 1. Local Handmade Suppliers (Artisans, Local Smiths)
        Schema::create('handmade_suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->decimal('opening_payable_balance', 10, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Handmade Purchases (Batch/Invoice Intake)
        Schema::create('handmade_purchases', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_code', 50)->unique();
            $table->foreignId('supplier_id')->constrained('handmade_suppliers')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('purchase_date');
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->decimal('paid_amount', 10, 2)->default(0.00);
            $table->decimal('due_amount', 10, 2)->default(0.00);
            $table->string('payment_method', 50)->default('cash'); // cash, bank_transfer, credit
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. Handmade Items (Tracks lifecycle, cost accumulation, and POS stock conversion)
        Schema::create('handmade_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('handmade_purchases')->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained('handmade_suppliers')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('category_lists')->nullOnDelete();
            $table->string('item_code', 50)->unique();
            $table->string('item_name');
            $table->enum('product_type', ['raw_product', 'raw_with_stone', 'final_sale_plated'])->default('raw_product');
            $table->enum('status', ['raw_in_stock', 'stone_setting', 'plating', 'ready_for_stock', 'in_pos_stock'])->default('raw_in_stock');
            $table->integer('quantity')->default(1);
            $table->decimal('weight', 8, 2)->nullable(); // in grams
            
            // Costs
            $table->decimal('purchase_cost', 10, 2);
            $table->decimal('stone_cost', 10, 2)->default(0.00);
            $table->string('stone_worker_notes')->nullable();
            $table->decimal('plating_cost', 10, 2)->default(0.00);
            $table->foreignId('plating_worker_id')->nullable()->constrained('gold_plating_workers')->nullOnDelete();
            $table->decimal('total_accumulated_cost', 10, 2);
            $table->decimal('selling_price', 10, 2)->nullable();

            // POS Stock Link
            $table->foreignId('product_detail_id')->nullable()->constrained('product_details')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 4. Payments given to Handmade Suppliers
        Schema::create('handmade_supplier_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('handmade_suppliers')->onDelete('cascade');
            $table->foreignId('purchase_id')->nullable()->constrained('handmade_purchases')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('payment_method', 50)->default('cash'); // cash, bank_transfer, online
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('handmade_supplier_payments');
        Schema::dropIfExists('handmade_items');
        Schema::dropIfExists('handmade_purchases');
        Schema::dropIfExists('handmade_suppliers');
    }
};
