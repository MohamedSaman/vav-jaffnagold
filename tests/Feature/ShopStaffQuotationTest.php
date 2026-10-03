<?php

namespace Tests\Feature;

use App\Livewire\ShopStaff\ShopStaffQuotationBilling;
use App\Livewire\ShopStaff\ShopStaffQuotationList;
use App\Livewire\ShopStaff\StoreBilling;
use App\Models\Customer;
use App\Models\POSSession;
use App\Models\ProductDetail;
use App\Models\ProductPrice;
use App\Models\ProductStock;
use App\Models\Quotation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class ShopStaffQuotationTest extends TestCase
{
    use DatabaseTransactions;

    protected $shopStaff;

    protected $walkingCustomer;

    protected $category;

    protected $brand;

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

        $this->category = \App\Models\CategoryList::create(['category_name' => 'Gold']);
        $this->brand = \App\Models\BrandList::create(['brand_name' => 'Jaffna']);

        // Create a POS session (required for store billing to run successfully)
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

    public function test_quotations_list_page_is_accessible()
    {
        $response = $this->get(route('shop-staff.quotation-list'));
        $response->assertStatus(200);
    }

    public function test_quotation_billing_page_is_accessible()
    {
        $response = $this->get(route('shop-staff.quotation-billing'));
        $response->assertStatus(200);
    }

    public function test_can_create_quotation_via_livewire()
    {
        // Setup product
        $product = ProductDetail::create([
            'name' => 'Gold Necklace',
            'code' => 'GN-001',
            'model' => 'Model-G',
            'brand_id' => $this->brand->id,
            'category_id' => $this->category->id,
        ]);
        ProductStock::create([
            'product_id' => $product->id,
            'available_stock' => 10,
        ]);
        ProductPrice::create([
            'product_id' => $product->id,
            'supplier_price' => 0,
            'selling_price' => 5000,
            'wholesale_price' => 4500,
            'distribute_price' => 4200,
        ]);

        Livewire::test(ShopStaffQuotationBilling::class)
            ->call('addToCart', [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'model' => $product->model,
                'image' => '',
                'retail_price' => 5000,
                'wholesale_price' => 4500,
                'distribute_price' => 4200,
                'discount_price' => 0,
                'stock' => 10,
                'is_variant' => false,
                'variant_id' => null,
                'variant_value' => null,
                'has_warranty' => 0,
                'warranty_duration' => null,
            ])
            ->assertSet('cart.0.id', $product->id)
            ->call('createQuotation')
            ->assertSet('showQuotationModal', true);

        $this->assertDatabaseHas('quotations', [
            'customer_name' => 'Walking Customer',
            'subtotal' => 5000,
            'total_amount' => 5000,
            'status' => 'draft',
            'created_by' => $this->shopStaff->id,
        ]);
    }

    public function test_can_delete_draft_quotation_but_cannot_delete_converted_quotation()
    {
        // Create a draft quotation
        $draftQuotation = Quotation::create([
            'customer_id' => $this->walkingCustomer->id,
            'customer_name' => $this->walkingCustomer->name,
            'customer_type' => 'retail',
            'quotation_date' => now(),
            'subtotal' => 2000,
            'total_amount' => 2000,
            'status' => 'draft',
            'items' => [],
            'valid_until' => now()->addDays(30),
            'created_by' => $this->shopStaff->id,
        ]);

        // Create a converted quotation
        $convertedQuotation = Quotation::create([
            'customer_id' => $this->walkingCustomer->id,
            'customer_name' => $this->walkingCustomer->name,
            'customer_type' => 'retail',
            'quotation_date' => now(),
            'subtotal' => 3000,
            'total_amount' => 3000,
            'status' => 'converted',
            'items' => [],
            'valid_until' => now()->addDays(30),
            'created_by' => $this->shopStaff->id,
        ]);

        // Delete draft quotation via component
        Livewire::test(ShopStaffQuotationList::class)
            ->call('deleteQuotation', $draftQuotation->id);

        $this->assertDatabaseMissing('quotations', ['id' => $draftQuotation->id]);

        // Attempt to delete converted quotation via component
        Livewire::test(ShopStaffQuotationList::class)
            ->call('deleteQuotation', $convertedQuotation->id);

        $this->assertDatabaseHas('quotations', ['id' => $convertedQuotation->id]);
    }

    public function test_convert_quotation_prefills_store_billing_and_completes_sale()
    {
        // Setup product
        $product = ProductDetail::create([
            'name' => 'Gold Ring',
            'code' => 'GR-002',
            'model' => 'Ring-M',
            'brand_id' => $this->brand->id,
            'category_id' => $this->category->id,
        ]);
        ProductStock::create([
            'product_id' => $product->id,
            'available_stock' => 15,
        ]);
        ProductPrice::create([
            'product_id' => $product->id,
            'supplier_price' => 0,
            'selling_price' => 3000,
        ]);

        // Create a quotation to convert
        $quotation = Quotation::create([
            'customer_id' => $this->walkingCustomer->id,
            'customer_name' => $this->walkingCustomer->name,
            'customer_type' => 'retail',
            'quotation_date' => now(),
            'subtotal' => 3000,
            'discount_amount' => 300,
            'additional_discount' => 300,
            'additional_discount_type' => 'fixed',
            'additional_discount_value' => 300,
            'total_amount' => 2700,
            'status' => 'draft',
            'items' => [
                [
                    'product_id' => $product->id,
                    'product_code' => $product->code,
                    'product_name' => $product->name,
                    'quantity' => 1,
                    'unit_price' => 3000,
                    'discount_per_unit' => 0,
                    'discount_percentage' => 0,
                    'total' => 3000,
                ],
            ],
            'valid_until' => now()->addDays(30),
            'created_by' => $this->shopStaff->id,
        ]);

        // Simulate going to StoreBilling with query parameter
        $test = Livewire::withQueryParams(['convert_quotation' => $quotation->id])
            ->test(StoreBilling::class)
            ->assertSet('convertedFromQuotationId', $quotation->id)
            ->assertSet('customerId', $this->walkingCustomer->id)
            ->assertCount('cart', 1)
            ->assertSet('cart.0.id', $product->id)
            ->assertSet('additionalDiscount', 300.0)
            ->assertSet('additionalDiscountType', 'fixed')
            ->assertSet('additionalDiscountAmount', 300.0);

        // Can adjust quantity without error
        $cartKey = $product->id.'-none';
        $test->call('incrementQuantity', $cartKey);
        $test->assertSet('cart.0.quantity', 2);
    }

    public function test_convert_quotation_with_custom_product_completes_sale()
    {
        $quotation = Quotation::create([
            'customer_id' => $this->walkingCustomer->id,
            'customer_name' => $this->walkingCustomer->name,
            'customer_type' => 'retail',
            'quotation_date' => now(),
            'subtotal' => 1500,
            'total_amount' => 1500,
            'status' => 'draft',
            'items' => [
                [
                    'product_id' => null,
                    'product_code' => 'CUSTOM-001',
                    'product_name' => 'Custom Handcrafted Bangle',
                    'quantity' => 1,
                    'unit_price' => 1500,
                    'discount_per_unit' => 0,
                    'discount_percentage' => 0,
                    'total' => 1500,
                    'is_custom' => true,
                ],
            ],
            'valid_until' => now()->addDays(30),
            'created_by' => $this->shopStaff->id,
        ]);

        Livewire::withQueryParams(['convert_quotation' => $quotation->id])
            ->test(StoreBilling::class)
            ->assertSet('convertedFromQuotationId', $quotation->id)
            ->assertCount('cart', 1)
            ->assertSet('cart.0.is_custom', true)
            ->assertSet('cart.0.name', 'Custom Handcrafted Bangle')
            ->set('paymentMethod', 'cash')
            ->set('amountReceived', 1500)
            ->call('createSale');

        $this->assertEquals('converted', $quotation->fresh()->status);
        $this->assertNotNull($quotation->fresh()->converted_at);
    }

    public function test_can_scan_barcode_and_product_code_to_add_to_cart()
    {
        $product = ProductDetail::create([
            'name' => 'Barcode Diamond Ring',
            'code' => 'BDR-777',
            'barcode' => '9988776655',
            'model' => 'Model-Ring',
            'brand_id' => $this->brand->id,
            'category_id' => $this->category->id,
        ]);
        ProductStock::create([
            'product_id' => $product->id,
            'available_stock' => 5,
        ]);
        ProductPrice::create([
            'product_id' => $product->id,
            'supplier_price' => 1000,
            'selling_price' => 1800,
            'wholesale_price' => 1500,
            'distributor_price' => 1400,
        ]);

        // 1. Scan by exact barcode
        Livewire::test(ShopStaffQuotationBilling::class)
            ->call('handleBarcodeOrSearch', '9988776655')
            ->assertCount('cart', 1)
            ->assertSet('cart.0.id', $product->id)
            ->assertSet('cart.0.barcode', '9988776655')
            ->assertSet('cart.0.code', 'BDR-777')
            ->assertSet('cart.0.quantity', 1)
            // Scan again -> increment
            ->call('handleBarcodeOrSearch', '9988776655')
            ->assertSet('cart.0.quantity', 2);

        // 2. Scan by exact product code
        Livewire::test(ShopStaffQuotationBilling::class)
            ->call('handleBarcodeOrSearch', 'BDR-777')
            ->assertCount('cart', 1)
            ->assertSet('cart.0.id', $product->id);
    }

    public function test_partial_search_finds_product_by_barcode_and_code()
    {
        $product = ProductDetail::create([
            'name' => 'Searchable Gold Bangle',
            'code' => 'SGB-888',
            'barcode' => '1122334455',
            'model' => 'Model-Bangle',
            'brand_id' => $this->brand->id,
            'category_id' => $this->category->id,
        ]);
        ProductStock::create([
            'product_id' => $product->id,
            'available_stock' => 8,
        ]);
        ProductPrice::create([
            'product_id' => $product->id,
            'supplier_price' => 2000,
            'selling_price' => 3500,
            'wholesale_price' => 3200,
            'distributor_price' => 3000,
        ]);

        // Search by partial barcode
        $test = Livewire::test(ShopStaffQuotationBilling::class)
            ->set('search', '112233');

        $results = $test->get('searchResults');
        $this->assertNotEmpty($results);
        $this->assertEquals($product->id, $results[0]['id']);

        // Search quotation list by product code
        $quotation = Quotation::create([
            'quotation_number' => 'QTN-TEST-SEARCH',
            'customer_id' => $this->walkingCustomer->id,
            'customer_name' => $this->walkingCustomer->name,
            'customer_type' => 'retail',
            'quotation_date' => now(),
            'subtotal' => 3500,
            'total_amount' => 3500,
            'status' => 'draft',
            'items' => [
                [
                    'product_id' => $product->id,
                    'product_code' => 'SGB-888',
                    'product_name' => 'Searchable Gold Bangle',
                    'quantity' => 1,
                    'unit_price' => 3500,
                    'total' => 3500,
                ],
            ],
            'valid_until' => now()->addDays(30),
            'created_by' => $this->shopStaff->id,
        ]);

        Livewire::test(ShopStaffQuotationList::class)
            ->set('search', 'SGB-888')
            ->assertSee('QTN-TEST-SEARCH');
    }

    public function test_can_enter_global_discount_directly_and_create_quotation()
    {
        $product = ProductDetail::create([
            'name' => 'Discount Test Bangle',
            'code' => 'DTB-101',
            'barcode' => '7788990011',
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
        ]);
        ProductStock::create([
            'product_id' => $product->id,
            'available_stock' => 10,
        ]);
        ProductPrice::create([
            'product_id' => $product->id,
            'supplier_price' => 5000,
            'selling_price' => 10000,
            'wholesale_price' => 9000,
            'distributor_price' => 8500,
        ]);

        $component = Livewire::test(ShopStaffQuotationBilling::class)
            ->call('addToCart', $product->id);

        $this->assertEquals(10000, $component->get('subtotal'));
        $this->assertEquals(0, $component->get('additionalDiscountAmount'));
        $this->assertEquals(10000, $component->get('grandTotal'));

        // Direct fixed discount entry
        $component->set('additionalDiscount', 1500);
        $this->assertEquals(1500, $component->get('additionalDiscountAmount'));
        $this->assertEquals(8500, $component->get('grandTotal'));

        // Toggle to percentage discount
        $component->call('setDiscountType', 'percentage')
            ->set('additionalDiscount', 10);
        $this->assertEquals(1000, $component->get('additionalDiscountAmount'));
        $this->assertEquals(9000, $component->get('grandTotal'));

        // Clear discount
        $component->call('clearAdditionalDiscount');
        $this->assertEquals(0, $component->get('additionalDiscountAmount'));
        $this->assertEquals(10000, $component->get('grandTotal'));

        // Set fixed discount again and create quotation
        $component->call('setDiscountType', 'fixed')
            ->set('additionalDiscount', 2000)
            ->call('createQuotation')
            ->assertSet('showQuotationModal', true);

        $quotation = Quotation::latest('id')->first();
        $this->assertNotNull($quotation);
        $this->assertEquals(10000, (float) $quotation->subtotal);
        $this->assertEquals(2000, (float) $quotation->additional_discount);
        $this->assertEquals(2000, (float) $quotation->additional_discount_value);
        $this->assertEquals('fixed', $quotation->additional_discount_type);
        $this->assertEquals(2000, (float) $quotation->discount_amount);
        $this->assertEquals(8000, (float) $quotation->total_amount);
    }
}
