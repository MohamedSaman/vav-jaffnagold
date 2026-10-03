<?php

namespace Tests\Feature;

use App\Livewire\ShopStaff\StoreBilling;
use App\Models\Customer;
use App\Models\POSSession;
use App\Models\ProductDetail;
use App\Models\ProductPrice;
use App\Models\ProductStock;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * QA Test: StoreBilling Discount Calculation
 *
 * Regression test for the bug where entering a discount on the 2nd product
 * in the cart was incorrectly applying to the 1st product (index 0).
 *
 * Root Cause: Alpine.js `x-data` initializes `val` only once on DOM mount.
 * After Livewire re-renders (e.g. after first discount update), Alpine reuses
 * the existing component state and `val` becomes stale — so the wrong index
 * was targeted. Fixed by switching to unique item keys instead of array indices.
 *
 * This test verifies the PHP-side `updateDiscount($itemKey, $discount)` correctly
 * targets each specific cart item by its unique key.
 */
class StoreBillingDiscountQaTest extends TestCase
{
    use DatabaseTransactions;

    protected $shopStaff;

    protected $walkingCustomer;

    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Create a shop staff user
        $this->shopStaff = $this->createShopStaffUser();

        // Create walking customer
        $this->walkingCustomer = Customer::firstOrCreate(
            ['name' => 'Walking Customer'],
            [
                'phone' => 'xxxxx',
                'email' => null,
                'address' => 'xxxxx',
                'type' => 'retail',
            ]
        );

        // Create a POS session so mount() doesn't block
        POSSession::updateOrCreate(
            [
                'user_id' => $this->shopStaff->id,
                'session_date' => now()->toDateString(),
            ],
            [
                'opening_cash' => 1000,
                'status' => 'open',
                'opened_at' => now(),
                'total_sales' => 0,
                'cash_sales' => 0,
                'cheque_payment' => 0,
                'credit_card_payment' => 0,
                'bank_transfer' => 0,
                'late_payment_bulk' => 0,
                'refunds' => 0,
                'expenses' => 0,
                'cash_deposit_bank' => 0,
                'expected_cash' => 0,
                'cash_difference' => 0,
            ]
        );

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->actingAs($this->shopStaff);
    }

    // -----------------------------------------------------------------------
    // Helper: build a minimal cart item array (simulates addToCart output)
    // -----------------------------------------------------------------------

    private function makeCartItem(string $key, int $productId, string $name, float $price, int $qty = 1, float $discountPct = 10): array
    {
        $discountAmount = round(($price * $discountPct) / 100, 2);

        return [
            'key' => $key,
            'id' => $productId,
            'product_id' => $productId,
            'variant_id' => null,
            'variant_value' => null,
            'name' => $name,
            'code' => 'CODE-'.$productId,
            'model' => null,
            'price' => $price,
            'quantity' => $qty,
            'discount' => $discountAmount,
            'discount_type' => 'percentage',
            'discount_percentage' => $discountPct,
            'total' => ($price - $discountAmount) * $qty,
            'stock' => 100,
            'pending' => 0,
            'image' => null,
            'batch_numbers' => null,
            'is_custom' => false,
        ];
    }

    // -----------------------------------------------------------------------
    // TEST 1: updateDiscount targets the correct item key
    // -----------------------------------------------------------------------

    public function test_update_discount_applies_to_correct_item_key(): void
    {
        $component = app(StoreBilling::class);
        $component->mount();

        // Simulate two products in cart (newest first — index 0 = Gold Ring, index 1 = Stud Earring)
        $component->cart = [
            $this->makeCartItem('cart_gold_ring', 2, 'Classic Gold Ring', 10000, 1, 10),   // index 0
            $this->makeCartItem('cart_stud_earring', 1, 'Stud Earring', 10000, 1, 10),     // index 1
        ];

        // ── Verify initial state ──
        $this->assertSame(10.0, (float) $component->cart[0]['discount_percentage']);
        $this->assertSame(10.0, (float) $component->cart[1]['discount_percentage']);

        // ── Apply 15% discount to Gold Ring using its KEY ──
        $component->updateDiscount('cart_gold_ring', '15%');

        $this->assertSame(15.0, (float) $component->cart[0]['discount_percentage'], 'Gold Ring should now have 15% discount');
        $this->assertSame(1500.0, (float) $component->cart[0]['discount']);
        $this->assertSame(8500.0, (float) $component->cart[0]['total']);

        // Stud Earring (index 1) must remain unchanged at 10%
        $this->assertSame(10.0, (float) $component->cart[1]['discount_percentage'], 'Stud Earring must STILL be at 10%');
    }

    // -----------------------------------------------------------------------
    // TEST 2: updateDiscount on second item by key must NOT touch first item
    // -----------------------------------------------------------------------

    public function test_update_discount_on_second_item_by_key_does_not_affect_first_item(): void
    {
        $component = app(StoreBilling::class);
        $component->mount();

        $component->cart = [
            $this->makeCartItem('cart_ring', 2, 'Classic Gold Ring', 10000, 1, 10),    // index 0
            $this->makeCartItem('cart_stud', 1, 'Stud Earring', 10000, 1, 10),    // index 1
        ];

        // Apply 15% discount to Stud Earring (the SECOND product) using its KEY
        $component->updateDiscount('cart_stud', '15%');

        // Stud Earring (index 1) should now have 15%
        $this->assertSame(15.0, (float) $component->cart[1]['discount_percentage'], 'Stud Earring should now be 15%');
        $this->assertSame(8500.0, (float) $component->cart[1]['total']);

        // Gold Ring (index 0) must remain at 10%
        $this->assertSame(10.0, (float) $component->cart[0]['discount_percentage'], 'Gold Ring must STILL be 10% — untouched');
        $this->assertSame(9000.0, (float) $component->cart[0]['total']);
    }

    // -----------------------------------------------------------------------
    // TEST 3: Prepending an item (shifting indices) works correctly with keys
    // -----------------------------------------------------------------------

    public function test_prepending_item_does_not_break_existing_item_discounts(): void
    {
        $component = app(StoreBilling::class);
        $component->mount();

        // Start with one item
        $component->cart = [
            $this->makeCartItem('k1', 1, 'Stud Earring', 10000, 1, 10),
        ];

        // Prepend another item (simulates addToCart)
        array_unshift($component->cart, $this->makeCartItem('k2', 2, 'Gold Ring', 10000, 1, 10));
        // Now: k2 is index 0, k1 is index 1

        // Update discount for k1 (the one that shifted)
        $component->updateDiscount('k1', '20%');

        $this->assertSame(20.0, (float) $component->cart[1]['discount_percentage'], 'Stud Earring (k1) should now have 20% discount');
        $this->assertSame(10.0, (float) $component->cart[0]['discount_percentage'], 'Gold Ring (k2) should still have 10% discount');
    }

    // -----------------------------------------------------------------------
    // TEST 4: Each item can have independent discount percentages
    // -----------------------------------------------------------------------

    public function test_each_cart_item_can_have_independent_discount(): void
    {
        $component = app(StoreBilling::class);
        $component->mount();

        $component->cart = [
            $this->makeCartItem('k0', 3, 'Necklace', 20000, 1, 10),
            $this->makeCartItem('k1', 2, 'Gold Ring', 10000, 1, 10),
            $this->makeCartItem('k2', 1, 'Stud Earring', 5000, 1, 10),
        ];

        // Apply different discounts using keys
        $component->updateDiscount('k0', '5%');
        $component->updateDiscount('k1', '15%');
        $component->updateDiscount('k2', '20%');

        $this->assertSame(5.0, (float) $component->cart[0]['discount_percentage']);
        $this->assertSame(15.0, (float) $component->cart[1]['discount_percentage']);
        $this->assertSame(20.0, (float) $component->cart[2]['discount_percentage']);
    }

    // -----------------------------------------------------------------------
    // TEST 5: Grand total reflects correct per-item discounts
    // -----------------------------------------------------------------------

    public function test_grand_total_reflects_correct_per_item_discounts(): void
    {
        $component = app(StoreBilling::class);
        $component->mount();

        $component->cart = [
            $this->makeCartItem('k0', 2, 'Classic Gold Ring', 10000, 1, 10),
            $this->makeCartItem('k1', 1, 'Stud Earring', 10000, 1, 10),
        ];

        $component->updateDiscount('k0', '15%');  // 10000 - 1500 = 8500
        // k1 stays at 10%: 9000

        $expectedGrandTotal = 8500.0 + 9000.0;
        $this->assertSame($expectedGrandTotal, (float) $component->grandTotal);
    }

    // -----------------------------------------------------------------------
    // TEST 6: Discount recalculates correctly after price update
    // -----------------------------------------------------------------------

    public function test_discount_amount_recalculates_when_price_changes(): void
    {
        $component = app(StoreBilling::class);
        $component->mount();

        $component->cart = [
            $this->makeCartItem('k0', 1, 'Ring', 10000, 1, 10),
        ];

        $component->updateDiscount('k0', '20%');
        $this->assertSame(2000.0, (float) $component->cart[0]['discount']);

        // Change price using KEY
        $component->updatePrice('k0', 5000);

        $this->assertSame(1000.0, (float) $component->cart[0]['discount'], 'Discount should recalculate as 20% of new price 5000');
        $this->assertSame(4000.0, (float) $component->cart[0]['total']);
    }

    // -----------------------------------------------------------------------
    // TEST 7: Direct Global Discount (Fixed and Percentage, Clear, and Toggle)
    // -----------------------------------------------------------------------

    public function test_direct_global_discount_fixed_and_percentage(): void
    {
        $component = app(StoreBilling::class);
        $component->mount();

        $component->cart = [
            $this->makeCartItem('k0', 1, 'Ring', 10000, 1, 0),
        ];

        // 1. Direct fixed discount of Rs. 1500
        $component->setDiscountType('fixed');
        $component->additionalDiscount = '1500';
        $component->updatedAdditionalDiscount('1500');

        $this->assertSame(1500.0, (float) $component->additionalDiscountAmount);
        $this->assertSame(8500.0, (float) $component->grandTotal);

        // 2. Direct percentage discount of 15%
        $component->setDiscountType('percentage');
        $component->additionalDiscount = '15';
        $component->updatedAdditionalDiscount('15');

        $this->assertSame(1500.0, (float) $component->additionalDiscountAmount);
        $this->assertSame(8500.0, (float) $component->grandTotal);

        // 3. Clear discount
        $component->clearAdditionalDiscount();
        $this->assertSame('', $component->additionalDiscount);
        $this->assertSame(0.0, (float) $component->additionalDiscountAmount);
        $this->assertSame(10000.0, (float) $component->grandTotal);
    }

    // -----------------------------------------------------------------------
    // TEST 8: Barcode and Product Code Scanning Adds Product Directly
    // -----------------------------------------------------------------------

    public function test_barcode_and_product_code_scanning_adds_product_to_cart(): void
    {
        $brandId = DB::table('brand_lists')->insertGetId([
            'brand_name' => 'Test Brand '.uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $catId = DB::table('category_lists')->insertGetId([
            'category_name' => 'Test Category '.uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $product = ProductDetail::create([
            'name' => 'Barcode POS Item',
            'code' => 'BPI-999',
            'barcode' => '888777666',
            'model' => 'Model-X',
            'brand_id' => $brandId,
            'category_id' => $catId,
        ]);
        ProductStock::create([
            'product_id' => $product->id,
            'available_stock' => 10,
        ]);
        ProductPrice::create([
            'product_id' => $product->id,
            'supplier_price' => 1000,
            'selling_price' => 2000,
            'wholesale_price' => 1800,
            'distributor_price' => 1700,
        ]);

        // 1. Scan barcode via handleBarcodeOrSearch
        Livewire::test(StoreBilling::class)
            ->call('handleBarcodeOrSearch', '888777666')
            ->assertCount('cart', 1)
            ->assertSet('cart.0.product_id', $product->id)
            ->assertSet('cart.0.code', 'BPI-999')
            ->assertSet('cart.0.barcode', '888777666')
            ->assertSet('cart.0.quantity', 1)
            // Scan again -> increments quantity
            ->call('handleBarcodeOrSearch', '888777666')
            ->assertSet('cart.0.quantity', 2);

        // 2. Scan product code via handleBarcodeOrSearch
        Livewire::test(StoreBilling::class)
            ->call('handleBarcodeOrSearch', 'BPI-999')
            ->assertCount('cart', 1)
            ->assertSet('cart.0.product_id', $product->id);
    }

    // -----------------------------------------------------------------------
    // TEST 9: Handles Over 100 Items in Cart Without Cracking or Extreme Latency
    // -----------------------------------------------------------------------

    public function test_handles_over_100_items_in_cart_without_lag_or_errors(): void
    {
        $component = app(StoreBilling::class);
        $component->mount();

        $items = [];
        for ($i = 1; $i <= 120; $i++) {
            $items[] = $this->makeCartItem("k{$i}", $i, "Product Item {$i}", 1000.0 + ($i * 10), 1, 5);
        }
        $component->cart = $items;

        $startTime = microtime(true);

        // 1. Assert subtotal and grandTotal calculate accurately for 120 items
        $this->assertCount(120, $component->cart);
        $this->assertGreaterThan(0, $component->subtotal);
        $this->assertGreaterThan(0, $component->grandTotal);

        // 2. Apply global discount
        $component->setDiscountType('percentage');
        $component->additionalDiscount = '10';
        $component->updatedAdditionalDiscount('10');

        $this->assertGreaterThan(0, $component->additionalDiscountAmount);
        $this->assertLessThan($component->subtotalAfterItemDiscounts, $component->grandTotal);

        // 3. Update single item by key in 120-item cart
        $component->updateDiscount('k50', '25%');
        $this->assertSame(25.0, (float) $component->cart[49]['discount_percentage']);

        // 4. Update price on 100th item
        $component->updatePrice('k100', 5000.0);
        $this->assertSame(5000.0, (float) $component->cart[99]['price']);

        $duration = microtime(true) - $startTime;

        // Ensure recalculations for 120 items complete well under 2 seconds
        $this->assertLessThan(2.0, $duration, "120-item cart calculations took {$duration}s, should be < 2s");
    }

    // -----------------------------------------------------------------------
    // TEST 10: Instant discount recalculation (User Screenshot Scenario: 10% -> 5%)
    // -----------------------------------------------------------------------

    public function test_instant_discount_recalculation_from_ten_to_five_percent(): void
    {
        $component = app(StoreBilling::class);
        $component->mount();

        // Simulate 2 items at price 7290 with initial 10% discount (exactly from user screenshot)
        $component->cart = [
            $this->makeCartItem('cart_test_item', 2, 'Czchnk 1920', 7290.0, 2, 10),
        ];

        // Initially at 10%: subtotal before discount = 14,580; discount = 1,458; total = 13,122
        $this->assertSame(13122.0, (float) $component->cart[0]['total']);
        $this->assertSame(13122.0, (float) $component->grandTotal);

        // User changes discount to 5%
        $component->updateDiscount('cart_test_item', '5');

        // New calculation: unit discount = 364.50; total discount = 729; total = 13,851
        $this->assertSame(5.0, (float) $component->cart[0]['discount_percentage']);
        $this->assertSame(364.5, (float) $component->cart[0]['discount']);
        $this->assertSame(13851.0, (float) $component->cart[0]['total']);
        $this->assertSame(13851.0, (float) $component->grandTotal);

        // Verify Livewire view renders real-time wire:input and instant recalc handlers
        Livewire::test(StoreBilling::class)
            ->set('cart', [
                $this->makeCartItem('k1', 2, 'Czchnk 1920', 7290.0, 2, 10),
            ])
            ->assertSeeHtml('wire:input.debounce.250ms="updateDiscount(\'k1\', $event.target.value)"')
            ->assertSeeHtml('recalcInstantDiscount');
    }

    // -----------------------------------------------------------------------
    // Helper: create shop_staff user
    // -----------------------------------------------------------------------

    private function createShopStaffUser()
    {
        $userId = DB::table('users')->insertGetId([
            'name' => 'Test Shop Staff',
            'email' => 'testshop_'.uniqid().'@example.com',
            'password' => bcrypt('password'),
            'role' => 'staff',
            'contact' => '1234567890',
            'staff_type' => 'shop_staff',
            'email_verified_at' => now(),
            'remember_token' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return \App\Models\User::find($userId);
    }
}
