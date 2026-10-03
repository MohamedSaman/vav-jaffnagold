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
        Schema::create('custom_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('tamil_name')->nullable();
            $table->string('slug')->unique();
            $table->string('category')->index(); // kodi, bangle, ring, bridal_thali, necklace
            $table->string('category_label');
            $table->string('purity')->default('22K (916 Hallmarked)');
            $table->text('description')->nullable();
            $table->json('pown_options'); // e.g. [5, 6, 7, 8]
            $table->decimal('min_pown', 8, 2);
            $table->decimal('max_pown', 8, 2);
            $table->decimal('base_pown', 8, 2)->default(5.00);
            $table->decimal('price_per_pown_estimate', 12, 2)->default(230000.00);
            $table->decimal('price_range_min', 12, 2);
            $table->decimal('price_range_max', 12, 2);
            $table->integer('making_days')->default(7);
            $table->string('image')->nullable();
            $table->json('gallery_images')->nullable();
            $table->json('specifications')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('custom_product_inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('inquiry_number')->unique();
            $table->foreignId('custom_product_id')->nullable()->constrained('custom_products')->nullOnDelete();
            $table->string('product_name');
            $table->string('category');
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            $table->string('city')->nullable();
            $table->decimal('selected_pown', 8, 2);
            $table->decimal('selected_grams', 8, 2);
            $table->string('size_specifications')->nullable(); // e.g. "Wrist size: 2.6 inches", "Ring size: 18", "Length: 26 inches"
            $table->string('gold_purity')->default('22K');
            $table->decimal('estimated_price', 12, 2)->default(0.00);
            $table->text('customer_notes')->nullable();
            $table->string('reference_image')->nullable();
            $table->string('status')->default('new'); // new, contacted, in_crafting, completed, cancelled
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_product_inquiries');
        Schema::dropIfExists('custom_products');
    }
};
