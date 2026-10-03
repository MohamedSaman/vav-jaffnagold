<?php

namespace Tests\Feature;

use App\Livewire\DeliveryMan\DeliveryManDashboard;
use App\Livewire\Ecommerce\WhatsAppOrderCheckout;
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
use App\Models\WhatsAppOrder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class WhatsAppOrderAndDeliveryHubTest extends TestCase
{
    use DatabaseTransactions;

    protected $deliveryUser;
    protected $shopStaffUser;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $deliveryUserId = DB::table('users')->insertGetId([
            'name'              => 'Delivery Man Test',
            'email'             => 'delman_' . uniqid() . '@example.com',
            'password'          => bcrypt('password'),
            'role'              => 'staff',
            'staff_type'        => 'delivery_man',
            'contact'           => '0771234567',
            'email_verified_at' => now(),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
        $this->deliveryUser = User::find($deliveryUserId);

        $shopStaffUserId = DB::table('users')->insertGetId([
            'name'              => 'Shop Staff Test',
            'email'             => 'staff_' . uniqid() . '@example.com',
            'password'          => bcrypt('password'),
            'role'              => 'staff',
            'staff_type'        => 'shop_staff',
            'contact'           => '0779998888',
            'email_verified_at' => now(),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
        $this->shopStaffUser = User::find($shopStaffUserId);

        $brand = BrandList::firstOrCreate(
            ['brand_name' => 'Jaffna Gold Brand'],
            ['status' => 'active']
        );

        $category = CategoryList::firstOrCreate(
            ['category_name' => 'Gold Bangles'],
            ['status' => 'active']
        );

        $supplier = ProductSupplier::firstOrCreate(
            ['email' => 'supplier_delivery_wa@test.com'],
            ['name' => 'Gold Supplier Lanka', 'contact' => '0779998877', 'address' => 'Colombo']
        );

        $this->product = ProductDetail::create([
            'name'             => '22k Gold Sovereign Bangle',
            'code'             => 'JGB-' . rand(1000, 9999),
            'brand_id'         => $brand->id,
            'category_id'      => $category->id,
            'status'           => 'active',
            'metal_type'       => 'gold',
            'carat'            => '22k',
            'weight'           => 8.00,
            'manufacturing_type' => 'machine',
            'barcode'          => 'BR' . rand(100000, 999999),
        ]);

        ProductPrice::create([
            'product_id'     => $this->product->id,
            'variant_id'     => null,
            'pricing_mode'   => 'single',
            'supplier_price' => 150000,
            'selling_price'  => 180000,
            'retail_price'   => 185000,
            'wholesale_price' => 175000,
            'discount_price' => 0,
        ]);

        ProductStock::create([
            'product_id'       => $this->product->id,
            'variant_id'       => null,
            'available_stock'  => 10,
            'damage_stock'     => 0,
            'total_stock'      => 10,
            'sold_count'       => 0,
            'restocked_quantity' => 0,
        ]);

        ProductBatch::create([
            'product_id'         => $this->product->id,
            'batch_number'       => 'BATCH-TEST-' . rand(100, 999),
            'quantity'           => 10,
            'remaining_quantity' => 10,
            'cost_price'         => 150000,
            'selling_price'      => 180000,
            'received_date'      => now(),
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /** @test */
    public function test_delivery_hub_excludes_regular_shop_staff_pos_walk_in_sales()
    {
        // 1. Create a regular in-store counter walk-in sale by shop staff
        $posSale = Sale::create([
            'sale_id'        => 'SL-POS-' . rand(1000, 9999),
            'invoice_number' => 'POS-INV-' . rand(1000, 9999),
            'sale_type'      => 'pos',
            'order_source'   => 'pos',
            'user_id'        => $this->shopStaffUser->id,
            'walking_customer_name' => 'Walking Customer Shop',
            'walking_customer_phone' => '0770001111',
            'subtotal'       => 50000,
            'total_amount'   => 50000,
            'paid_amount'    => 50000,
            'due_amount'     => 0,
            'payment_type'   => 'full',
            'payment_status' => 'paid',
            'delivery_status' => 'pending', // migration default
        ]);

        // 2. Create a WhatsApp delivery sale
        $deliverySaleRecord = Sale::create([
            'sale_id'        => 'SL-WA-' . rand(1000, 9999),
            'invoice_number' => 'WA-INV-' . rand(1000, 9999),
            'sale_type'      => 'staff',
            'order_source'   => 'whatsapp',
            'user_id'        => $this->deliveryUser->id,
            'walking_customer_name' => 'WhatsApp Delivery Customer',
            'walking_customer_phone' => '0772223333',
            'subtotal'       => 180000,
            'total_amount'   => 180450,
            'paid_amount'    => 0,
            'due_amount'     => 180450,
            'payment_type'   => 'partial',
            'payment_status' => 'pending',
            'delivery_status' => 'pending',
        ]);

        DeliverySale::create([
            'sale_id'          => $deliverySaleRecord->id,
            'delivery_barcode' => 'DLV-TEST-001',
            'delivery_method'  => 'Post',
            'payment_method'   => 'Cash on Delivery',
            'delivery_charge'  => 450,
            'status'           => 'Processing',
            'customer_details' => "Customer: WhatsApp Delivery Customer\nPhone: 0772223333\nAddress: 123 Point Pedro Road, Jaffna",
        ]);

        $this->actingAs($this->deliveryUser);

        // Test Livewire Dashboard
        $component = Livewire::test(DeliveryManDashboard::class);

        // The WhatsApp delivery sale should be present
        $component->assertSee('WhatsApp Delivery Customer');

        // The regular POS walk-in sale by shop staff must NOT be present
        $component->assertDontSee('Walking Customer Shop');
    }

    /** @test */
    public function test_whatsapp_order_link_generation_and_customer_checkout_flow()
    {
        $this->actingAs($this->deliveryUser);

        // 1. Staff generates WhatsApp Link in Delivery Dashboard
        $component = Livewire::test(DeliveryManDashboard::class)
            ->call('openWhatsAppLinkModal')
            ->call('addProductToWhatsAppOrder', $this->product->id)
            ->set('waCustomerName', 'Ravi Kumar')
            ->set('waCustomerPhone', '0775556677')
            ->set('waDeliveryCharge', 500)
            ->set('waNotes', 'Gift wrap please')
            ->call('generateWhatsAppOrderLink');

        $component->assertDispatched('show-toast');

        $waOrder = WhatsAppOrder::where('customer_phone', '0775556677')->first();
        $this->assertNotNull($waOrder);
        $this->assertEquals(185000, $waOrder->subtotal);
        $this->assertEquals(185500, $waOrder->total_amount);
        $this->assertEquals('pending_customer', $waOrder->status);
        $this->assertNotNull($waOrder->token);

        // 2. Customer opens checkout page with the generated token
        $initialStock = ProductStock::where('product_id', $this->product->id)->first()->available_stock;

        $checkoutTest = Livewire::test(WhatsAppOrderCheckout::class, ['token' => $waOrder->token])
            ->assertSee('22k Gold Sovereign Bangle')
            ->assertSee('Rs. 185,500')
            ->set('customer_name', 'Ravi Kumar')
            ->set('customer_phone', '0775556677')
            ->set('address_line1', '45 Hospital Road')
            ->set('city', 'Jaffna')
            ->set('postal_code', '40000')
            ->set('delivery_method', 'Post')
            ->set('payment_method', 'Cash on Delivery')
            ->call('submitOrder');

        $checkoutTest->assertSet('orderConfirmed', true);

        // 3. Verify Sale & DeliverySale created
        $waOrder->refresh();
        $this->assertEquals('completed', $waOrder->status);
        $this->assertNotNull($waOrder->sale_id);

        $sale = Sale::find($waOrder->sale_id);
        $this->assertNotNull($sale);
        $this->assertEquals('whatsapp', $sale->order_source);
        $this->assertEquals('Ravi Kumar', $sale->customer_display_name);
        $this->assertEquals('0775556677', $sale->customer_display_phone);
        $this->assertEquals('pending', $sale->delivery_status);

        $deliverySale = DeliverySale::where('sale_id', $sale->id)->first();
        $this->assertNotNull($deliverySale);
        $this->assertStringStartsWith('DLV', $deliverySale->delivery_barcode);
        $this->assertEquals('Cash on Delivery', $deliverySale->payment_method);

        // 4. Verify Stock was deducted
        $remainingStock = ProductStock::where('product_id', $this->product->id)->first()->available_stock;
        $this->assertEquals($initialStock - 1, $remainingStock);

        // 5. Verify order appears in Delivery Dashboard
        Livewire::test(DeliveryManDashboard::class)
            ->assertSee('Ravi Kumar')
            ->assertSee('WhatsApp')
            ->assertSee($deliverySale->delivery_barcode);
    }

    /** @test */
    public function test_parcel_workflow_pack_and_status_transitions()
    {
        $sale = Sale::create([
            'sale_id'        => 'SL-DLV-' . rand(1000, 9999),
            'invoice_number' => 'TEST-DLV-' . rand(1000, 9999),
            'sale_type'      => 'staff',
            'order_source'   => 'delivery_man',
            'user_id'        => $this->deliveryUser->id,
            'walking_customer_name' => 'Saman Perera',
            'walking_customer_phone' => '0712345678',
            'subtotal'       => 50000,
            'total_amount'   => 50450,
            'paid_amount'    => 0,
            'due_amount'     => 50450,
            'payment_type'   => 'partial',
            'payment_status' => 'pending',
            'delivery_status' => 'pending',
        ]);

        $deliverySale = DeliverySale::create([
            'sale_id'          => $sale->id,
            'delivery_barcode' => 'DLV-TEST-999',
            'delivery_method'  => 'Domex',
            'payment_method'   => 'Cash on Delivery',
            'delivery_charge'  => 450,
            'status'           => 'Processing',
            'customer_details' => "Customer: Saman Perera\nPhone: 0712345678\nAddress: Colombo 07",
        ]);

        $this->actingAs($this->deliveryUser);

        // 1. Mark as Packed
        Livewire::test(DeliveryManDashboard::class)
            ->call('openStatusModal', $sale->id, 'packed')
            ->call('executeStatusChange');

        $sale->refresh();
        $this->assertEquals('packed', $sale->delivery_status);

        // 2. Dispatch with Tracking Number -> In Transit
        Livewire::test(DeliveryManDashboard::class)
            ->call('openStatusModal', $sale->id, 'in_transit')
            ->set('courierTrackingNumber', 'DMX-TRACK-12345')
            ->call('executeStatusChange');

        $sale->refresh();
        $deliverySale->refresh();
        $this->assertEquals('in_transit', $sale->delivery_status);
        $this->assertEquals('DMX-TRACK-12345', $deliverySale->tracking_number);

        // 3. Mark Delivered and Collect COD Payment
        Livewire::test(DeliveryManDashboard::class)
            ->call('openStatusModal', $sale->id, 'delivered')
            ->call('executeStatusChange')
            ->call('openPaymentModal', $sale->id)
            ->set('paymentAmount', 50450)
            ->set('paymentMethod', 'cash')
            ->call('savePayment');

        $sale->refresh();
        $this->assertEquals('delivered', $sale->delivery_status);
        $this->assertEquals('paid', $sale->payment_status);
        $this->assertEquals(0, $sale->due_amount);
    }

    /** @test */
    public function test_direct_delivery_sale_creation_from_modal()
    {
        $this->actingAs($this->deliveryUser);

        $initialStock = ProductStock::where('product_id', $this->product->id)->first()->available_stock;

        $comp = Livewire::test(DeliveryManDashboard::class)
            ->call('openCreateSaleModal')
            ->call('addProductToDirectSale', $this->product->id)
            ->set('directCustomerName', 'Kamal Silva')
            ->set('directCustomerPhone', '0761112233')
            ->set('directCustomerAddress', 'Temple Road, Nallur')
            ->set('directDeliveryMethod', 'Post')
            ->set('directPaymentMethod', 'Cash on Delivery')
            ->set('directDeliveryCharge', 450)
            ->call('saveDirectDeliverySale');

        $comp->assertDispatched('show-toast');

        $createdSale = Sale::where('walking_customer_phone', '0761112233')->first();
        $this->assertNotNull($createdSale);
        $this->assertEquals('delivery_man', $createdSale->order_source);
        $this->assertEquals('Kamal Silva', $createdSale->customer_display_name);

        $deliverySale = DeliverySale::where('sale_id', $createdSale->id)->first();
        $this->assertNotNull($deliverySale);
        $this->assertEquals('Cash on Delivery', $deliverySale->payment_method);

        // Stock was reduced
        $remainingStock = ProductStock::where('product_id', $this->product->id)->first()->available_stock;
        $this->assertEquals($initialStock - 1, $remainingStock);
    }

    /** @test */
    public function test_whatsapp_order_link_generation_with_item_and_order_discounts()
    {
        $this->actingAs($this->deliveryUser);

        // Retail price of product is 185,000.
        // Let's add product, apply 5,000 item discount, and 2,000 order discount.
        // Delivery charge = 500.
        // Expected subtotal = 185,000
        // Expected discount_amount = 7,000 (5,000 + 2,000)
        // Expected total = (185,000 - 7,000) + 500 = 178,500
        $comp = Livewire::test(DeliveryManDashboard::class)
            ->call('openWhatsAppLinkModal')
            ->call('addProductToWhatsAppOrder', $this->product->id)
            ->call('updateWhatsAppItemDiscount', 0, 5000)
            ->set('waOrderDiscount', 2000)
            ->set('waDeliveryCharge', 500)
            ->set('waCustomerName', 'Discounted Customer')
            ->set('waCustomerPhone', '0778887766')
            ->call('generateWhatsAppOrderLink');

        $comp->assertDispatched('show-toast');

        $waOrder = WhatsAppOrder::where('customer_phone', '0778887766')->first();
        $this->assertNotNull($waOrder);
        $this->assertEquals(185000, (float) $waOrder->subtotal);
        $this->assertEquals(7000, (float) $waOrder->discount_amount);
        $this->assertEquals(500, (float) $waOrder->delivery_charge);
        $this->assertEquals(178500, (float) $waOrder->total_amount);

        // Now simulate Customer checking out on the link
        $checkout = Livewire::test(WhatsAppOrderCheckout::class, ['token' => $waOrder->token])
            ->assertSee('Discount Savings')
            ->assertSee('7,000.00')
            ->assertSee('178,500.00')
            ->set('customer_name', 'Discounted Customer')
            ->set('customer_phone', '0778887766')
            ->set('address_line1', '35 Hospital Road')
            ->set('city', 'Jaffna')
            ->set('payment_method', 'Cash on Delivery')
            ->call('submitOrder');

        $checkout->assertSet('orderConfirmed', true);

        // Check created sale
        $sale = Sale::where('walking_customer_phone', '0778887766')->first();
        $this->assertNotNull($sale);
        $this->assertEquals(185000, (float) $sale->subtotal);
        $this->assertEquals(7000, (float) $sale->discount_amount);
        $this->assertEquals(178500, (float) $sale->total_amount);

        // Check sale item discount
        $saleItem = $sale->items()->first();
        $this->assertNotNull($saleItem);
        $this->assertEquals(5000, (float) $saleItem->discount_per_unit);
        $this->assertEquals(5000, (float) $saleItem->total_discount);
    }

    /** @test */
    public function test_product_suggestions_only_show_when_typing_and_domex_is_default()
    {
        $this->actingAs($this->deliveryUser);

        // 1. When opening modal with empty search, no products are suggested
        $test = Livewire::test(DeliveryManDashboard::class)
            ->call('openWhatsAppLinkModal')
            ->assertSet('productSearch', '')
            ->assertViewHas('availableProducts', function ($products) {
                return count($products) === 0;
            });

        // 2. When user types a search term, suggestions appear
        $test->set('productSearch', 'Gold')
            ->assertViewHas('availableProducts', function ($products) {
                return count($products) > 0;
            });

        // 3. When adding the product, search resets to empty and suggestions close
        $test->call('addProductToWhatsAppOrder', $this->product->id)
            ->assertSet('productSearch', '')
            ->assertViewHas('availableProducts', function ($products) {
                return count($products) === 0;
            });

        // 4. Domex is the default courier method for direct sales and checkout
        $test->call('openCreateSaleModal')
            ->assertSet('directDeliveryMethod', 'Domex');

        $checkout = Livewire::test(WhatsAppOrderCheckout::class, ['token' => 'dummy_token']);
        $this->assertEquals('Domex', $checkout->get('delivery_method'));
    }

    /** @test */
    public function test_instant_custom_products_in_whatsapp_and_direct_sale()
    {
        $this->actingAs($this->deliveryUser);

        // 1. WhatsApp Order with Instant Custom Product
        $waTest = Livewire::test(DeliveryManDashboard::class)
            ->call('openWhatsAppLinkModal')
            ->set('waCustomerName', 'Custom WA Customer')
            ->set('waCustomerPhone', '0773334455')
            ->set('waCustomItemName', 'Handmade 22k Custom Ring')
            ->set('waCustomItemPrice', 85000)
            ->set('waCustomItemCode', 'CUST-RING-99')
            ->set('waCustomItemQty', 2)
            ->set('waCustomItemDiscount', 2000)
            ->call('addCustomProductToWhatsAppOrder')
            ->assertCount('selectedWhatsAppItems', 1);

        // Verify custom item payload
        $items = $waTest->get('selectedWhatsAppItems');
        $this->assertNull($items[0]['product_id']);
        $this->assertEquals('Handmade 22k Custom Ring', $items[0]['name']);
        $this->assertEquals('CUST-RING-99', $items[0]['code']);
        $this->assertEquals(2, $items[0]['quantity']);
        $this->assertEquals(85000, $items[0]['unit_price']);
        $this->assertEquals(2000, $items[0]['discount']);
        $this->assertEquals(166000, $items[0]['subtotal']); // (85000 - 2000) * 2

        $waTest->set('waDeliveryCharge', 500)
            ->call('generateWhatsAppOrderLink');

        $waOrder = WhatsAppOrder::where('customer_phone', '0773334455')->first();
        $this->assertNotNull($waOrder);
        $this->assertEquals(170000, (float) $waOrder->subtotal); // 85000 * 2
        $this->assertEquals(4000, (float) $waOrder->discount_amount); // 2000 * 2
        $this->assertEquals(166500, (float) $waOrder->total_amount); // 166000 + 500

        // Complete Checkout for custom item WhatsApp order
        Livewire::test(WhatsAppOrderCheckout::class, ['token' => $waOrder->token])
            ->set('customer_name', 'Custom WA Customer')
            ->set('customer_phone', '0773334455')
            ->set('address_line1', '45 Temple Road')
            ->set('city', 'Jaffna')
            ->set('payment_method', 'Cash on Delivery')
            ->call('submitOrder')
            ->assertSet('orderConfirmed', true);

        $waSale = Sale::where('walking_customer_phone', '0773334455')->first();
        $this->assertNotNull($waSale);
        $waSaleItem = $waSale->items()->first();
        $this->assertNotNull($waSaleItem);
        $this->assertNull($waSaleItem->product_id);
        $this->assertEquals('Handmade 22k Custom Ring', $waSaleItem->product_name);
        $this->assertEquals('CUST-RING-99', $waSaleItem->product_code);
        $this->assertEquals(166000, (float) $waSaleItem->total);

        // 2. Direct Delivery Sale with Instant Custom Product
        $directTest = Livewire::test(DeliveryManDashboard::class)
            ->call('openCreateSaleModal')
            ->set('directCustomerName', 'Direct Custom Client')
            ->set('directCustomerPhone', '0774445566')
            ->set('directCustomerAddress', '10 Main Street, Jaffna')
            ->set('directDeliveryMethod', 'Domex')
            ->set('directPaymentMethod', 'Cash on Delivery')
            ->set('directDeliveryCharge', 450)
            ->set('customItemName', 'Custom Gold Chain Pendant')
            ->set('customItemPrice', 120000)
            ->set('customItemCode', 'CUST-PD-01')
            ->set('customItemQty', 1)
            ->set('customItemDiscount', 5000)
            ->call('addCustomProductToDirectSale')
            ->assertCount('directSaleItems', 1);

        $directItems = $directTest->get('directSaleItems');
        $this->assertNull($directItems[0]['product_id']);
        $this->assertEquals('Custom Gold Chain Pendant', $directItems[0]['name']);
        $this->assertEquals(115000, $directItems[0]['subtotal']); // 120000 - 5000

        $directTest->call('saveDirectDeliverySale');

        $directSale = Sale::where('walking_customer_phone', '0774445566')->first();
        $this->assertNotNull($directSale);
        $this->assertEquals(120000, (float) $directSale->subtotal);
        $this->assertEquals(5000, (float) $directSale->discount_amount);
        $this->assertEquals(115450, (float) $directSale->total_amount); // 115000 + 450

        $directSaleItem = $directSale->items()->first();
        $this->assertNotNull($directSaleItem);
        $this->assertNull($directSaleItem->product_id);
        $this->assertEquals('Custom Gold Chain Pendant', $directSaleItem->product_name);
        $this->assertEquals(115000, (float) $directSaleItem->total);
    }
}

