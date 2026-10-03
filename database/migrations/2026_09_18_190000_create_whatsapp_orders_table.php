<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create whatsapp_orders table for custom shareable order links
        if (!Schema::hasTable('whatsapp_orders')) {
            Schema::create('whatsapp_orders', function (Blueprint $table) {
                $table->id();
                $table->string('token', 64)->unique()->index();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->string('customer_name')->nullable();
                $table->string('customer_phone')->nullable();
                $table->json('items'); // JSON payload: [{product_id, name, code, image, quantity, unit_price, subtotal}]
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->decimal('delivery_charge', 15, 2)->default(450);
                $table->decimal('total_amount', 15, 2)->default(0);
                $table->text('notes')->nullable();
                $table->string('status', 30)->default('pending_customer'); // pending_customer, completed, expired, cancelled
                $table->unsignedBigInteger('sale_id')->nullable()->index();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();

                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            });
        }

        // 2. Add order_source and ecommerce_order_id to sales table if not exists
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'order_source')) {
                $table->string('order_source', 50)->default('pos')->after('sale_type');
            }
            if (!Schema::hasColumn('sales', 'ecommerce_order_id')) {
                $table->unsignedBigInteger('ecommerce_order_id')->nullable()->after('order_source');
            }
        });

        // 3. Add ecommerce_order_id and slip_image to delivery_sales table if not exists
        Schema::table('delivery_sales', function (Blueprint $table) {
            if (!Schema::hasColumn('delivery_sales', 'ecommerce_order_id')) {
                $table->unsignedBigInteger('ecommerce_order_id')->nullable()->after('sale_id');
            }
            if (!Schema::hasColumn('delivery_sales', 'slip_image')) {
                $table->string('slip_image')->nullable()->after('customer_details');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_orders');

        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'order_source')) {
                $table->dropColumn('order_source');
            }
            if (Schema::hasColumn('sales', 'ecommerce_order_id')) {
                $table->dropColumn('ecommerce_order_id');
            }
        });

        Schema::table('delivery_sales', function (Blueprint $table) {
            if (Schema::hasColumn('delivery_sales', 'ecommerce_order_id')) {
                $table->dropColumn('ecommerce_order_id');
            }
            if (Schema::hasColumn('delivery_sales', 'slip_image')) {
                $table->dropColumn('slip_image');
            }
        });
    }
};
