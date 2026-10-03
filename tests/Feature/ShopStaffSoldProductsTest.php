<?php

namespace Tests\Feature;

use App\Livewire\ShopStaff\SoldProducts;
use App\Models\BrandList;
use App\Models\CategoryList;
use App\Models\Customer;
use App\Models\ProductDetail;
use App\Models\ProductPrice;
use App\Models\ProductStock;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class ShopStaffSoldProductsTest extends TestCase
{
    use DatabaseTransactions;

    protected $shopStaff;
    protected $category;
    protected $brand;
    protected $customer;

    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $userId = DB::table('users')->insertGetId([
            'name'              => 'Test Staff Analyst',
            'email'             => 'staff_analytics_' . uniqid() . '@example.com',
            'password'          => bcrypt('password'),
            'role'              => 'staff',
            'contact'           => '0770001122',
            'staff_type'        => 'shop_staff',
            'email_verified_at' => now(),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        $this->shopStaff = User::find($userId);

        $this->category = CategoryList::firstOrCreate(['category_name' => 'Jewelry']);
        $this->brand = BrandList::firstOrCreate(['brand_name' => 'Jaffna Gold Brand']);

        $this->customer = Customer::firstOrCreate(
            ['name' => 'Kavitha Sivan'],
            ['phone' => '0779991122', 'type' => 'retail']
        );

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->actingAs($this->shopStaff);
    }

    public function test_sold_products_page_is_accessible(): void
    {
        $response = $this->get(route('shop-staff.sold-products'));
        $response->assertStatus(200);
        $response->assertSee('Sold Products Overview');
        $response->assertSee('Local (Handmade)');
        $response->assertSee('Indian (Imported)');
    }

    public function test_separates_local_and_indian_products_into_two_columns(): void
    {
        // 1. Create Local (Handmade) Product
        $localProduct = ProductDetail::create([
            'code' => 'SL-RING-001',
            'name' => 'Local Peacock Ring',
            'model' => 'Handmade Jewelry',
            'origin' => 'local',
            'brand_id' => $this->brand->id,
            'category_id' => $this->category->id,
            'barcode' => 'SL-RING-001',
        ]);
        ProductStock::create([
            'product_id' => $localProduct->id,
            'available_stock' => 12,
            'total_stock' => 20,
            'sold_count' => 8,
        ]);
        ProductPrice::create([
            'product_id' => $localProduct->id,
            'supplier_price' => 3000,
            'selling_price' => 5000,
            'retail_price' => 5000,
        ]);

        // 2. Create Indian (Imported) Product
        $indianProduct = ProductDetail::create([
            'code' => 'IN-BANGLE-001',
            'name' => 'Indian Machine Bangle Set',
            'model' => 'Machine Bangle',
            'origin' => 'indian',
            'brand_id' => $this->brand->id,
            'category_id' => $this->category->id,
            'barcode' => 'IN-BANGLE-001',
        ]);
        ProductStock::create([
            'product_id' => $indianProduct->id,
            'available_stock' => 25,
            'total_stock' => 40,
            'sold_count' => 15,
        ]);
        ProductPrice::create([
            'product_id' => $indianProduct->id,
            'supplier_price' => 1000,
            'selling_price' => 2000,
            'retail_price' => 2000,
        ]);

        // 3. Create Sales
        $sale = Sale::create([
            'sale_id' => 'SALE-TEST-001',
            'invoice_number' => 'INV-TEST-001',
            'customer_id' => $this->customer->id,
            'user_id' => $this->shopStaff->id,
            'subtotal' => 70000,
            'total_amount' => 70000,
            'payment_type' => 'full',
            'payment_status' => 'paid',
            'status' => 'confirm',
        ]);

        // Sale item for local
        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $localProduct->id,
            'product_code' => $localProduct->code,
            'product_name' => $localProduct->name,
            'product_model' => $localProduct->model,
            'quantity' => 8,
            'unit_price' => 5000,
            'total' => 40000,
        ]);

        // Sale item for Indian
        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $indianProduct->id,
            'product_code' => $indianProduct->code,
            'product_name' => $indianProduct->name,
            'product_model' => $indianProduct->model,
            'quantity' => 15,
            'unit_price' => 2000,
            'total' => 30000,
        ]);

        // Test Livewire component
        $component = Livewire::test(SoldProducts::class)
            ->set('datePreset', 'today');

        // View assertions
        $component->assertSee('Local Peacock Ring');
        $component->assertSee('Indian Machine Bangle Set');
        $component->assertSee('SL-RING-001');
        $component->assertSee('IN-BANGLE-001');

        // Verify component view data properties
        $component->assertViewHas('localTotalQty', 8);
        $component->assertViewHas('localTotalAmount', 40000.0);
        $component->assertViewHas('indianTotalQty', 15);
        $component->assertViewHas('indianTotalAmount', 30000.0);
        $component->assertViewHas('overallTotalQty', 23);
        $component->assertViewHas('overallTotalAmount', 70000.0);
    }

    public function test_correctly_ranks_highest_sold_products(): void
    {
        // Create 2 local products: Top seller with 10 sold, Second with 3 sold
        $productTop = ProductDetail::create([
            'code' => 'SL-TOP-001',
            'name' => 'Best Seller Local Kodi',
            'model' => 'Handmade Jewelry',
            'origin' => 'local',
            'brand_id' => $this->brand->id,
            'category_id' => $this->category->id,
        ]);

        $productSecond = ProductDetail::create([
            'code' => 'SL-SECOND-002',
            'name' => 'Second Local Ring',
            'model' => 'Handmade Jewelry',
            'origin' => 'local',
            'brand_id' => $this->brand->id,
            'category_id' => $this->category->id,
        ]);

        $sale = Sale::create([
            'sale_id' => 'SALE-RANK-TEST',
            'invoice_number' => 'INV-RANK-TEST',
            'user_id' => $this->shopStaff->id,
            'subtotal' => 65000,
            'total_amount' => 65000,
            'payment_type' => 'full',
            'payment_status' => 'paid',
            'status' => 'confirm',
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $productTop->id,
            'product_code' => $productTop->code,
            'product_name' => $productTop->name,
            'product_model' => $productTop->model,
            'quantity' => 10,
            'unit_price' => 5000,
            'total' => 50000,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $productSecond->id,
            'product_code' => $productSecond->code,
            'product_name' => $productSecond->name,
            'product_model' => $productSecond->model,
            'quantity' => 3,
            'unit_price' => 5000,
            'total' => 15000,
        ]);

        Livewire::test(SoldProducts::class)
            ->set('datePreset', 'all_time')
            ->set('sortBy', 'qty_desc')
            ->assertViewHas('localProducts', function ($localProducts) {
                return $localProducts->first()->product_code === 'SL-TOP-001'
                    && $localProducts->first()->rank === 1
                    && $localProducts->first()->total_qty_sold === 10
                    && $localProducts->get(1)->product_code === 'SL-SECOND-002'
                    && $localProducts->get(1)->rank === 2;
            });
    }

    public function test_can_search_and_filter_sold_products(): void
    {
        $localProduct = ProductDetail::create([
            'code' => 'SL-UNIQUE-CHAIN',
            'name' => 'Handcrafted Ruby Chain',
            'model' => 'Handmade Jewelry',
            'origin' => 'local',
            'brand_id' => $this->brand->id,
            'category_id' => $this->category->id,
        ]);

        $sale = Sale::create([
            'sale_id' => 'SALE-SEARCH-TEST',
            'invoice_number' => 'INV-SEARCH-TEST',
            'user_id' => $this->shopStaff->id,
            'subtotal' => 25000,
            'total_amount' => 25000,
            'payment_type' => 'full',
            'payment_status' => 'paid',
            'status' => 'confirm',
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $localProduct->id,
            'product_code' => $localProduct->code,
            'product_name' => $localProduct->name,
            'product_model' => $localProduct->model,
            'quantity' => 5,
            'unit_price' => 5000,
            'total' => 25000,
        ]);

        Livewire::test(SoldProducts::class)
            ->set('datePreset', 'all_time')
            ->set('search', 'Ruby Chain')
            ->assertSee('Handcrafted Ruby Chain')
            ->set('search', 'NonExistentProductXYZ')
            ->assertDontSee('Handcrafted Ruby Chain');
    }

    public function test_can_open_and_view_sales_history_modal(): void
    {
        $product = ProductDetail::create([
            'code' => 'IN-MODAL-TEST',
            'name' => 'Indian Diamond Necklace',
            'model' => 'Necklace',
            'origin' => 'indian',
            'brand_id' => $this->brand->id,
            'category_id' => $this->category->id,
        ]);

        $sale = Sale::create([
            'sale_id' => 'SALE-MODAL-001',
            'invoice_number' => 'INV-MODAL-999',
            'customer_id' => $this->customer->id,
            'user_id' => $this->shopStaff->id,
            'subtotal' => 12000,
            'total_amount' => 12000,
            'payment_type' => 'full',
            'payment_status' => 'paid',
            'status' => 'confirm',
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'product_code' => $product->code,
            'product_name' => $product->name,
            'product_model' => $product->model,
            'quantity' => 2,
            'unit_price' => 6000,
            'total' => 12000,
        ]);

        Livewire::test(SoldProducts::class)
            ->set('datePreset', 'all_time')
            ->call('openHistoryModal', $product->id, $product->code, $product->name)
            ->assertSet('showHistoryModal', true)
            ->assertSee('INV-MODAL-999')
            ->assertSee('Indian Diamond Necklace')
            ->call('closeHistoryModal')
            ->assertSet('showHistoryModal', false);
    }
}
