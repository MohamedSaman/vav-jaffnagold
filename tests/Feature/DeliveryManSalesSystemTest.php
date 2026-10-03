<?php

namespace Tests\Feature;

use App\Livewire\DeliveryMan\DeliveryManSalesSystem;
use App\Models\BrandList;
use App\Models\CategoryList;
use App\Models\Customer;
use App\Models\DeliverySale;
use App\Models\ProductDetail;
use App\Models\ProductPrice;
use App\Models\ProductStock;
use App\Models\ProductBatch;
use App\Models\ProductSupplier;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class DeliveryManSalesSystemTest extends TestCase
{
    use DatabaseTransactions;

    protected $deliveryUser;
    protected $brand;
    protected $category;
    protected $supplier;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $userId = DB::table('users')->insertGetId([
            'name'              => 'Test Delivery Man',
            'email'             => 'delivery_' . uniqid() . '@example.com',
            'password'          => bcrypt('password'),
            'role'              => 'staff',
            'staff_type'        => 'delivery_man',
            'contact'           => '0771234567',
            'email_verified_at' => now(),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        $this->deliveryUser = User::find($userId);

        $this->brand = BrandList::firstOrCreate(
            ['brand_name' => 'Gold Test Brand'],
            ['status' => 'active']
        );

        $this->category = CategoryList::firstOrCreate(
            ['category_name' => 'Gold Test Category'],
            ['status' => 'active']
        );

        $this->supplier = ProductSupplier::firstOrCreate(
            ['email' => 'supplier_delivery@test.com'],
            ['name' => 'Delivery Test Supplier', 'contact' => '0779998877', 'address' => 'Colombo']
        );

        // Create test product with stock batch and price
        $this->product = ProductDetail::create([
            'code' => 'TEST-DLV-001',
            'name' => 'Gold Necklace 22K',
            'model' => 'Standard',
            'barcode' => '9988776655443',
            'status' => 'active',
            'brand_id' => $this->brand->id,
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
        ]);

        ProductPrice::create([
            'product_id' => $this->product->id,
            'variant_id' => null,
            'pricing_mode' => 'single',
            'supplier_price' => 50000,
            'selling_price' => 60000,
            'retail_price' => 60000,
            'wholesale_price' => 55000,
            'discount_price' => 0,
        ]);

        ProductStock::create([
            'product_id' => $this->product->id,
            'variant_id' => null,
            'available_stock' => 10,
            'damage_stock' => 0,
            'total_stock' => 10,
            'sold_count' => 0,
            'restocked_quantity' => 0,
        ]);

        ProductBatch::create([
            'product_id' => $this->product->id,
            'batch_number' => 'BATCH-' . uniqid(),
            'quantity' => 10,
            'remaining_quantity' => 10,
            'cost_price' => 50000,
            'selling_price' => 60000,
            'received_date' => now(),
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /** @test */
    public function delivery_man_can_access_sales_system_route()
    {
        $this->actingAs($this->deliveryUser);

        $response = $this->get(route('delivery.sales-system'));
        $response->assertStatus(200);
        $response->assertSee('Billing System');
    }

    /** @test */
    public function delivery_man_can_search_inventory_products()
    {
        $this->actingAs($this->deliveryUser);

        Livewire::test(DeliveryManSalesSystem::class)
            ->set('search', 'Gold Necklace')
            ->assertCount('searchResults', 1)
            ->assertSet('searchResults.0.id', $this->product->id)
            ->assertSet('searchResults.0.name', 'Gold Necklace 22K');
    }

    /** @test */
    public function delivery_man_can_create_delivery_sale_with_customer_name_and_number()
    {
        $this->actingAs($this->deliveryUser);

        $component = Livewire::test(DeliveryManSalesSystem::class)
            ->set('customerName', 'Kamal Perera')
            ->set('customerPhone', '0771234567')
            ->set('customerAddress', 'No 12, Main Street, Jaffna')
            ->set('deliveryMethod', 'Post')
            ->set('paymentMethod', 'Cash on Delivery')
            ->set('deliveryCharge', 450)
            ->call('addToCart', [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'code' => $this->product->code,
                'model' => $this->product->model,
                'price' => 60000,
                'stock' => 10,
                'variant_id' => null,
                'variant_value' => null,
            ])
            ->call('createSale');

        $component->assertHasNoErrors();

        // Verify Customer record was created in DB
        $savedCustomer = Customer::where('phone', '0771234567')->first();
        $this->assertNotNull($savedCustomer);
        $this->assertEquals('Kamal Perera', $savedCustomer->name);
        $this->assertEquals('No 12, Main Street, Jaffna', $savedCustomer->address);

        // Verify Sale created in DB and linked to customer
        $sale = Sale::where('user_id', $this->deliveryUser->id)
            ->orderBy('id', 'desc')
            ->first();

        $this->assertNotNull($sale);
        $this->assertEquals($savedCustomer->id, $sale->customer_id);
        $this->assertEquals('Kamal Perera', $sale->walking_customer_name);
        $this->assertEquals('0771234567', $sale->walking_customer_phone);
        $this->assertEquals('confirm', $sale->status);
        $this->assertEquals($this->deliveryUser->id, $sale->delivered_by);
        $this->assertEquals('pending', $sale->delivery_status);

        // Verify DeliverySale record
        $deliverySale = DeliverySale::where('sale_id', $sale->id)->first();
        $this->assertNotNull($deliverySale);
        $this->assertEquals('Post', $deliverySale->delivery_method);
        $this->assertEquals('Cash on Delivery', $deliverySale->payment_method);
        $this->assertEquals(450, $deliverySale->delivery_charge);
        $this->assertEquals('Processing', $deliverySale->status);
        $this->assertStringContainsString('Kamal Perera', $deliverySale->customer_details);
        $this->assertStringContainsString('0771234567', $deliverySale->customer_details);
        $this->assertNotEmpty($deliverySale->delivery_barcode);
    }

    /** @test */
    public function delivery_man_can_access_print_routes()
    {
        $this->actingAs($this->deliveryUser);

        $customer = Customer::firstOrCreate(
            ['name' => 'Print Test Customer'],
            ['phone' => '0770001122', 'type' => 'retail', 'user_id' => $this->deliveryUser->id]
        );

        $sale = Sale::create([
            'sale_id' => Sale::generateSaleId(),
            'invoice_number' => Sale::generateInvoiceNumber(),
            'customer_id' => $customer->id,
            'walking_customer_name' => 'Print Test Customer',
            'walking_customer_phone' => '0770001122',
            'customer_type' => 'retail',
            'subtotal' => 60000,
            'discount_amount' => 0,
            'total_amount' => 60450,
            'payment_type' => 'full',
            'payment_status' => 'paid',
            'due_amount' => 0,
            'user_id' => $this->deliveryUser->id,
            'delivered_by' => $this->deliveryUser->id,
            'delivery_status' => 'pending',
            'status' => 'confirm',
            'sale_type' => 'staff',
        ]);

        DeliverySale::create([
            'sale_id' => $sale->id,
            'delivery_method' => 'Post',
            'payment_method' => 'Cash on Delivery',
            'delivery_charge' => 450,
            'status' => 'Processing',
            'customer_details' => "Customer: Print Test Customer\nPhone: 0770001122\nAddress: Test Customer Address",
        ]);

        $responseSale = $this->get(route('delivery.print.sale', $sale->id));
        $responseSale->assertStatus(200);
        $responseSale->assertSee('Print Test Customer');
        $responseSale->assertSee('0770001122');

        $responseLabel = $this->get(route('delivery.print.delivery-label', $sale->id));
        $responseLabel->assertStatus(200);
        $responseLabel->assertSee('Print Test Customer');
        $responseLabel->assertSee('0770001122');
    }

    /** @test */
    public function pending_deliveries_displays_customer_name_phone_and_address()
    {
        $this->actingAs($this->deliveryUser);

        $customer = Customer::firstOrCreate(
            ['phone' => '0778899001'],
            ['name' => 'Pending Delivery Customer', 'address' => '55 Beach Road, Jaffna', 'type' => 'retail', 'user_id' => $this->deliveryUser->id]
        );

        $sale = Sale::create([
            'sale_id' => Sale::generateSaleId(),
            'invoice_number' => Sale::generateInvoiceNumber(),
            'customer_id' => $customer->id,
            'walking_customer_name' => 'Pending Delivery Customer',
            'walking_customer_phone' => '0778899001',
            'customer_type' => 'retail',
            'subtotal' => 10000,
            'discount_amount' => 0,
            'total_amount' => 10450,
            'payment_type' => 'full',
            'payment_status' => 'paid',
            'due_amount' => 0,
            'user_id' => $this->deliveryUser->id,
            'delivered_by' => $this->deliveryUser->id,
            'delivery_status' => 'pending',
            'order_source' => 'delivery_man',
            'status' => 'confirm',
            'sale_type' => 'staff',
        ]);

        DeliverySale::create([
            'sale_id' => $sale->id,
            'delivery_method' => 'Post',
            'payment_method' => 'Cash on Delivery',
            'delivery_charge' => 450,
            'status' => 'Processing',
            'customer_details' => "Customer: Pending Delivery Customer\nPhone: 0778899001\nAddress: 55 Beach Road, Jaffna",
        ]);

        $response = $this->followingRedirects()->get(route('delivery.pending'));
        $response->assertStatus(200);
        $response->assertSee('Pending Delivery Customer');
        $response->assertSee('0778899001');

        // Test Livewire component search
        \Livewire\Livewire::test(\App\Livewire\DeliveryMan\DeliveryManPendingDeliveries::class)
            ->set('search', '0778899001')
            ->assertSee('Pending Delivery Customer')
            ->call('viewDetails', $sale->id)
            ->assertSee('Pending Delivery Customer')
            ->assertSee('0778899001');
    }

    /** @test */
    public function completed_deliveries_and_dashboard_display_customer_name_and_phone()
    {
        $this->actingAs($this->deliveryUser);

        $customer = Customer::firstOrCreate(
            ['phone' => '0773344556'],
            ['name' => 'Completed Delivery Customer', 'address' => '10 Hospital Road, Jaffna', 'type' => 'retail', 'user_id' => $this->deliveryUser->id]
        );

        $sale = Sale::create([
            'sale_id' => Sale::generateSaleId(),
            'invoice_number' => Sale::generateInvoiceNumber(),
            'customer_id' => $customer->id,
            'walking_customer_name' => 'Completed Delivery Customer',
            'walking_customer_phone' => '0773344556',
            'customer_type' => 'retail',
            'subtotal' => 15000,
            'discount_amount' => 0,
            'total_amount' => 15450,
            'payment_type' => 'full',
            'payment_status' => 'paid',
            'due_amount' => 0,
            'user_id' => $this->deliveryUser->id,
            'delivered_by' => $this->deliveryUser->id,
            'delivery_status' => 'delivered',
            'order_source' => 'delivery_man',
            'delivered_at' => now(),
            'status' => 'confirm',
            'sale_type' => 'staff',
        ]);

        DeliverySale::create([
            'sale_id' => $sale->id,
            'delivery_method' => 'Post',
            'payment_method' => 'Cash on Delivery',
            'delivery_charge' => 450,
            'status' => 'Delivered',
            'customer_details' => "Customer: Completed Delivery Customer\nPhone: 0773344556\nAddress: 10 Hospital Road, Jaffna",
        ]);

        // Check Completed Deliveries
        $responseCompleted = $this->followingRedirects()->get(route('delivery.completed'));
        $responseCompleted->assertStatus(200);
        $responseCompleted->assertSee('Completed Delivery Customer');
        $responseCompleted->assertSee('0773344556');

        // Check Dashboard
        $responseDash = $this->get(route('delivery.dashboard'));
        $responseDash->assertStatus(200);
        $responseDash->assertSee('Completed Delivery Customer');
        $responseDash->assertSee('0773344556');
    }
}
