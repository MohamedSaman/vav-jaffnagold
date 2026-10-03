<?php

namespace Database\Seeders;

use App\Models\BrandList;
use App\Models\CategoryList;
use App\Models\Customer;
use App\Models\ProductDetail;
use App\Models\ProductPrice;
use App\Models\ProductStock;
use App\Models\ProductSupplier;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class JewelryShopPosSeeder extends Seeder
{
    /**
     * Seed realistic POS demo data for a jewelry shop.
     */
    public function run(): void
    {
        $faker = fake();

        $categories = [
            'Gold Rings',
            'Necklaces',
            'Bracelets',
            'Earrings',
            'Watches',
        ];

        $categoryMap = [];
        foreach ($categories as $categoryName) {
            $category = CategoryList::firstOrCreate(['category_name' => $categoryName]);
            $categoryMap[$categoryName] = $category;
        }

        $brands = [
            'Ceylon Gold',
            'Royal Jewelers',
            'Golden Aura',
            'Lanka Luxe',
            'Heritage Gold',
            'Starline Timepieces',
            'Classic 22K',
            'Prestige Ornaments',
        ];

        $brandIds = [];
        foreach ($brands as $brandName) {
            $brandIds[] = BrandList::firstOrCreate(['brand_name' => $brandName])->id;
        }

        $suppliers = collect();
        for ($i = 1; $i <= 10; $i++) {
            $suppliers->push(ProductSupplier::create([
                'name' => $faker->name(),
                'businessname' => $faker->company() . ' Gems',
                'contact' => $faker->phoneNumber(),
                'address' => $faker->address(),
                'email' => $faker->unique()->safeEmail(),
                'phone' => $faker->phoneNumber(),
                'status' => 'active',
                'notes' => 'Supplies premium jewelry lines and watches.',
                'overpayment' => 0,
            ]));
        }

        $customers = collect();
        for ($i = 1; $i <= 20; $i++) {
            $customerType = $faker->randomElement(['retail', 'wholesale', 'distributor']);

            $customers->push(Customer::create([
                'name' => $faker->name(),
                'email' => $faker->unique()->safeEmail(),
                'phone' => $faker->phoneNumber(),
                'address' => $faker->address(),
                'type' => $customerType,
                'notes' => $faker->sentence(),
                'business_name' => $customerType === 'retail' ? null : $faker->company(),
                'opening_balance' => 0,
                'due_amount' => 0,
                'total_due' => 0,
                'overpaid_amount' => 0,
            ]));
        }

        $productTypes = [
            [
                'category' => 'Gold Rings',
                'patterns' => ['Classic', 'Bridal', 'Floral', 'Twisted', 'Halo'],
                'weight_min' => 2.0,
                'weight_max' => 12.0,
                'price_min' => 80000,
                'price_max' => 450000,
                'stock_min' => 3,
                'stock_max' => 40,
            ],
            [
                'category' => 'Necklaces',
                'patterns' => ['Temple', 'Modern', 'Layered', 'Traditional', 'Pendant'],
                'weight_min' => 8.0,
                'weight_max' => 45.0,
                'price_min' => 180000,
                'price_max' => 1200000,
                'stock_min' => 1,
                'stock_max' => 20,
            ],
            [
                'category' => 'Bracelets',
                'patterns' => ['Chain', 'Cuff', 'Charm', 'Link', 'Bangle'],
                'weight_min' => 6.0,
                'weight_max' => 30.0,
                'price_min' => 90000,
                'price_max' => 500000,
                'stock_min' => 2,
                'stock_max' => 25,
            ],
            [
                'category' => 'Earrings',
                'patterns' => ['Stud', 'Drop', 'Hoop', 'Jhumka', 'Pearl'],
                'weight_min' => 1.5,
                'weight_max' => 10.0,
                'price_min' => 45000,
                'price_max' => 250000,
                'stock_min' => 5,
                'stock_max' => 60,
            ],
            [
                'category' => 'Watches',
                'patterns' => ['Chronograph', 'Classic Dial', 'Skeleton', 'Sport', 'Luxury'],
                'weight_min' => 25.0,
                'weight_max' => 120.0,
                'price_min' => 30000,
                'price_max' => 600000,
                'stock_min' => 2,
                'stock_max' => 25,
            ],
        ];

        $products = collect();

        for ($i = 1; $i <= 50; $i++) {
            $type = $faker->randomElement($productTypes);
            $category = $categoryMap[$type['category']];
            $weight = round($faker->randomFloat(2, $type['weight_min'], $type['weight_max']), 2);
            $sellingPrice = (float) $faker->numberBetween($type['price_min'], $type['price_max']);
            $supplierPrice = round($sellingPrice * $faker->randomFloat(2, 0.72, 0.90), 2);
            $discountPrice = $faker->boolean(35)
                ? round($sellingPrice * $faker->randomFloat(2, 0.92, 0.98), 2)
                : null;
            $availableStock = $faker->numberBetween($type['stock_min'], $type['stock_max']);
            $damageStock = $faker->numberBetween(0, 2);

            $supplier = $suppliers->random();
            $productCode = sprintf('JG-%s-%03d', Carbon::now()->format('ymd'), $i);

            $product = ProductDetail::create([
                'code' => $productCode,
                'name' => $type['patterns'][array_rand($type['patterns'])] . ' ' . rtrim($type['category'], 's'),
                'model' => '22K',
                'description' => sprintf('Weight: %.2fg | Handmade finish', $weight),
                'barcode' => $faker->unique()->ean13(),
                'status' => 'active',
                'sale_bonus' => round($sellingPrice * 0.01, 2),
                'brand_id' => $faker->randomElement($brandIds),
                'category_id' => $category->id,
                'supplier_id' => $supplier->id,
                'variant_id' => null,
                'website_live' => false,
            ]);

            ProductPrice::create([
                'product_id' => $product->id,
                'variant_id' => null,
                'variant_value' => null,
                'pricing_mode' => 'single',
                'supplier_price' => $supplierPrice,
                'selling_price' => $sellingPrice,
                'retail_price' => $sellingPrice,
                'wholesale_price' => round($sellingPrice * 0.96, 2),
                'distributor_price' => round($sellingPrice * 0.92, 2),
                'discount_price' => $discountPrice,
            ]);

            ProductStock::create([
                'product_id' => $product->id,
                'variant_id' => null,
                'variant_value' => null,
                'available_stock' => $availableStock,
                'damage_stock' => $damageStock,
                'total_stock' => $availableStock + $damageStock,
                'sold_count' => 0,
                'restocked_quantity' => $availableStock,
            ]);

            $products->push($product->load(['price', 'stock']));
        }

        $saleUserId = User::query()->value('id') ?? User::factory()->create()->id;

        DB::transaction(function () use ($faker, $customers, $products, $saleUserId): void {
            for ($i = 1; $i <= 100; $i++) {
                $customer = $customers->random();
                $saleDate = Carbon::now()->subDays($faker->numberBetween(0, 180));
                $itemCount = $faker->numberBetween(1, 5);

                $selectedProducts = $products->shuffle()->take($itemCount);

                $subtotal = 0;
                $preparedItems = [];

                foreach ($selectedProducts as $product) {
                    $unitPrice = (float) ($product->price->discount_price ?? $product->price->selling_price);
                    $maxQty = max(1, min(3, (int) $product->stock->available_stock));
                    $quantity = $faker->numberBetween(1, $maxQty);
                    $lineTotal = round($unitPrice * $quantity, 2);

                    $preparedItems[] = [
                        'product_id' => $product->id,
                        'product_code' => $product->code,
                        'product_name' => $product->name,
                        'product_model' => $product->model,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'discount_per_unit' => 0,
                        'total_discount' => 0,
                        'discount_type' => 'fixed',
                        'discount_percentage' => 0,
                        'total' => $lineTotal,
                        'variant_id' => null,
                        'variant_value' => null,
                        'has_warranty' => false,
                        'warranty_duration' => null,
                    ];

                    $subtotal += $lineTotal;
                }

                $discountAmount = $faker->boolean(25)
                    ? round($subtotal * $faker->randomFloat(2, 0.01, 0.06), 2)
                    : 0;
                $totalAmount = max(0, round($subtotal - $discountAmount, 2));

                $isPartial = $faker->boolean(20);
                $dueAmount = $isPartial ? round($totalAmount * $faker->randomFloat(2, 0.10, 0.50), 2) : 0;

                $sale = Sale::create([
                    'sale_id' => Sale::generateSaleId(),
                    'invoice_number' => Sale::generateInvoiceNumber(),
                    'sale_type' => 'pos',
                    'customer_id' => $customer->id,
                    'customer_type' => $customer->type,
                    'subtotal' => $subtotal,
                    'discount_amount' => $discountAmount,
                    'discount_type' => 'fixed',
                    'additional_discount_type' => 'fixed',
                    'additional_discount_percentage' => 0,
                    'total_amount' => $totalAmount,
                    'payment_type' => $isPartial ? 'partial' : 'full',
                    'payment_status' => $isPartial ? 'partial' : 'paid',
                    'status' => 'confirm',
                    'due_amount' => $dueAmount,
                    'notes' => $faker->sentence(),
                    'user_id' => $saleUserId,
                    'created_at' => $saleDate,
                    'updated_at' => $saleDate,
                ]);

                foreach ($preparedItems as $itemData) {
                    $itemData['sale_id'] = $sale->id;
                    $itemData['created_at'] = $saleDate;
                    $itemData['updated_at'] = $saleDate;

                    SaleItem::create($itemData);

                    $stock = ProductStock::where('product_id', $itemData['product_id'])
                        ->whereNull('variant_id')
                        ->whereNull('variant_value')
                        ->first();

                    if ($stock) {
                        $stock->available_stock = max(0, $stock->available_stock - $itemData['quantity']);
                        $stock->sold_count += $itemData['quantity'];
                        $stock->total_stock = $stock->available_stock + $stock->damage_stock;
                        $stock->save();
                    }
                }
            }
        });
    }
}
