<?php

namespace Tests\Feature;

use App\Livewire\Admin\StoreBilling as AdminStoreBilling;
use App\Livewire\ShopStaff\StoreBilling as ShopStaffStoreBilling;
use App\Models\Customer;
use App\Models\POSSession;
use App\Models\ProductDetail;
use App\Models\ProductPrice;
use App\Models\ProductStock;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class StoreBillingPerformanceAndKeyboardQaTest extends TestCase
{
    use DatabaseTransactions;

    protected $adminUser;
    protected $staffUser;
    protected $walkingCustomer;
    protected $product1;
    protected $product2;

    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->staffUser = User::factory()->create([
            'role' => 'shop_staff',
        ]);

        $this->walkingCustomer = Customer::firstOrCreate(
            ['name' => 'Walking Customer'],
            [
                'phone' => 'xxxxx',
                'email' => null,
                'address' => 'xxxxx',
                'type' => 'retail',
            ]
        );

        POSSession::updateOrCreate(
            [
                'user_id' => $this->adminUser->id,
                'session_date' => now()->toDateString(),
            ],
            [
                'opening_cash' => 0,
                'status' => 'open',
                'opened_at' => now(),
            ]
        );

        POSSession::updateOrCreate(
            [
                'user_id' => $this->staffUser->id,
                'session_date' => now()->toDateString(),
            ],
            [
                'opening_cash' => 0,
                'status' => 'open',
                'opened_at' => now(),
            ]
        );

        // Product 1
        $this->product1 = ProductDetail::create([
            'name' => 'Gold Chain QA',
            'code' => 'GC-001-QA',
            'barcode' => 'BAR-GC-001',
            'model' => 'GC-M1',
            'category_id' => 1,
            'brand_id' => 1,
        ]);
        ProductStock::create([
            'product_id' => $this->product1->id,
            'available_stock' => 20,
            'total_stock' => 20,
        ]);
        ProductPrice::create([
            'product_id' => $this->product1->id,
            'supplier_price' => 3000,
            'selling_price' => 5000,
            'retail_price' => 5000,
            'wholesale_price' => 4500,
            'distributor_price' => 4200,
        ]);

        // Product 2
        $this->product2 = ProductDetail::create([
            'name' => 'Gold Ring QA',
            'code' => 'GR-002-QA',
            'barcode' => 'BAR-GR-002',
            'model' => 'GR-M2',
            'category_id' => 1,
            'brand_id' => 1,
        ]);
        ProductStock::create([
            'product_id' => $this->product2->id,
            'available_stock' => 15,
            'total_stock' => 15,
        ]);
        ProductPrice::create([
            'product_id' => $this->product2->id,
            'supplier_price' => 4000,
            'selling_price' => 8000,
            'retail_price' => 8000,
            'wholesale_price' => 7500,
            'distributor_price' => 7200,
        ]);
    }

    public function test_admin_store_billing_handles_barcode_scan_instantly()
    {
        $this->actingAs($this->adminUser);

        Livewire::test(AdminStoreBilling::class)
            ->call('handleBarcodeOrSearch', 'BAR-GC-001')
            ->assertCount('cart', 1)
            ->assertSet('cart.0.code', 'GC-001-QA')
            ->assertSet('cart.0.price', 5000);
    }

    public function test_admin_store_billing_handles_product_code_enter_instantly()
    {
        $this->actingAs($this->adminUser);

        Livewire::test(AdminStoreBilling::class)
            ->call('handleBarcodeOrSearch', 'GR-002-QA')
            ->assertCount('cart', 1)
            ->assertSet('cart.0.code', 'GR-002-QA')
            ->assertSet('cart.0.price', 8000);
    }

    public function test_admin_store_billing_keyboard_arrow_select_and_add_by_index()
    {
        $this->actingAs($this->adminUser);

        // Search for "Gold" -> returns both product1 and product2
        $test = Livewire::test(AdminStoreBilling::class)
            ->set('search', 'Gold QA')
            ->assertCount('searchResults', 2);

        // User pressed Arrow Down to index 1 ("Gold Ring QA") and pressed Enter
        $test->call('addToCartByIndex', 1)
            ->assertCount('cart', 1)
            ->assertSet('cart.0.name', 'Gold Ring QA')
            ->assertSet('search', '')
            ->assertCount('searchResults', 0);
    }

    public function test_admin_store_billing_processing_lock_prevents_double_submission()
    {
        $this->actingAs($this->adminUser);

        $test = Livewire::test(AdminStoreBilling::class)
            ->call('handleBarcodeOrSearch', 'BAR-GC-001')
            ->assertCount('cart', 1);

        // Set isProcessing = true simulating an active in-flight request
        $test->set('isProcessing', true);

        $initialSalesCount = Sale::count();

        // Calling createSale or validateAndCreateSale while isProcessing must immediately return
        $test->call('validateAndCreateSale');
        $this->assertEquals($initialSalesCount, Sale::count());

        $test->call('createSale');
        $this->assertEquals($initialSalesCount, Sale::count());
    }

    public function test_admin_store_billing_empty_cart_cannot_create_sale()
    {
        $this->actingAs($this->adminUser);

        $initialSalesCount = Sale::count();

        Livewire::test(AdminStoreBilling::class)
            ->assertCount('cart', 0)
            ->call('createSale');

        $this->assertEquals($initialSalesCount, Sale::count());
    }

    public function test_shop_staff_store_billing_keyboard_and_barcode_parity()
    {
        $this->actingAs($this->staffUser);

        // Test barcode scan
        $test = Livewire::test(ShopStaffStoreBilling::class)
            ->call('handleBarcodeOrSearch', 'BAR-GR-002')
            ->assertCount('cart', 1)
            ->assertSet('cart.0.code', 'GR-002-QA');

        // Test search and add by index
        $test->set('search', 'Gold Chain QA')
            ->assertCount('searchResults', 1)
            ->call('addToCartByIndex', 0)
            ->assertCount('cart', 2)
            ->assertSet('search', '')
            ->assertCount('searchResults', 0);
    }
}
