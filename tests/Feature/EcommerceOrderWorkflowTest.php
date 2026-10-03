<?php

namespace Tests\Feature;

use App\Livewire\DeliveryMan\DeliveryOrderDetail;
use App\Livewire\Ecommerce\Checkout;
use App\Livewire\Ecommerce\ProductDetail as ProductDetailComponent;
use App\Models\BrandList;
use App\Models\CategoryList;
use App\Models\Customer;
use App\Models\DeliverySale;
use App\Models\EcommerceOrder;
use App\Models\Payment;
use App\Models\ProductDetail;
use App\Models\ProductPrice;
use App\Models\ProductStock;
use App\Models\ProductSupplier;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class EcommerceOrderWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    protected $product;

    protected $deliveryUser;

    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $brand = BrandList::firstOrCreate(['brand_name' => 'Jaffna Gold Crafts']);
        $cat = CategoryList::firstOrCreate(['category_name' => 'Traditional Bangles']);
        $supplier = ProductSupplier::firstOrCreate(
            ['name' => 'Northern Gold House'],
            ['contact' => '0212223344', 'email' => 'north@jaffnagold.lk', 'address' => 'Hospital Road, Jaffna']
        );

        $this->product = ProductDetail::create([
            'name' => '22K Handcrafted Gold Bangle',
            'code' => 'BGL-'.uniqid(),
            'barcode' => 'BC'.rand(100000, 999999),
            'brand_id' => $brand->id,
            'category_id' => $cat->id,
            'supplier_id' => $supplier->id,
            'slug' => '22k-handcrafted-gold-bangle-'.uniqid(),
            'status' => 'active',
            'website_live' => true,
            'image' => 'products/main_bangle.webp',
            'images' => [
                'products/angle_1.webp',
                'products/angle_2.webp',
                'products/craft_close.webp',
            ],
        ]);

        ProductPrice::create([
            'product_id' => $this->product->id,
            'supplier_price' => 120000,
            'retail_price' => 145000,
            'selling_price' => 145000,
        ]);

        ProductStock::create([
            'product_id' => $this->product->id,
            'total_stock' => 10,
            'available_stock' => 10,
            'damage_stock' => 0,
            'sold_count' => 0,
        ]);

        // Create a delivery staff user
        $this->deliveryUser = User::create([
            'name' => 'Ranjan Delivery',
            'email' => 'delivery_'.uniqid().'@jaffnagold.lk',
            'contact' => '0777123456',
            'role' => 'staff',
            'staff_type' => 'delivery_man',
            'password' => bcrypt('password123'),
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        Auth::logout();
        session()->flush();
    }

    /** @test */
    public function checkout_creates_order_without_reducing_stock_and_uses_simplified_inputs()
    {
        session()->put('cart', [
            $this->product->id => 2,
        ]);

        $checkout = new Checkout;
        $checkout->mount();

        $this->assertCount(1, $checkout->cartItems);
        $this->assertEquals(2, $checkout->cartItems[0]['quantity']);

        // Test city suggestions
        $checkout->city = 'jaf';
        $checkout->updatedCity();
        $this->assertTrue($checkout->showCitySuggestions);
        $this->assertNotEmpty($checkout->citySuggestions);
        $this->assertContains('Jaffna', $checkout->citySuggestions);

        // Select city
        $checkout->selectCity('Jaffna');
        $this->assertEquals('Jaffna', $checkout->city);
        $this->assertFalse($checkout->showCitySuggestions);

        // Fill in only the 4 simplified customer inputs
        $customerPhone = '0779911223';
        $checkout->full_name = 'Kugan Sivalingam';
        $checkout->phone = $customerPhone;
        $checkout->address = '78 Clock Tower Road';
        $checkout->city = 'Jaffna';
        $checkout->payment_method = 'COD';

        $checkout->placeOrder();

        // 1. Assert EcommerceOrder created
        $order = EcommerceOrder::where('customer_phone', $customerPhone)->latest()->first();
        $this->assertNotNull($order, 'E-commerce order must be created');
        $this->assertEquals('Kugan Sivalingam', $order->customer_name);
        $this->assertEquals('78 Clock Tower Road', $order->shipping_address);
        $this->assertEquals('Jaffna', $order->city);
        $this->assertEquals('pending', $order->order_status);

        // 2. Assert linked Sale and DeliverySale created
        $sale = Sale::where('ecommerce_order_id', $order->id)->first();
        $this->assertNotNull($sale, 'Linked Sale must be created');
        $this->assertEquals('pending', $sale->status);

        $deliverySale = DeliverySale::where('sale_id', $sale->id)->first();
        $this->assertNotNull($deliverySale, 'DeliverySale record must be created');
        $this->assertEquals('Domex', $deliverySale->delivery_method);

        // 3. CRITICAL: Stock must NOT be deducted at checkout!
        $stock = ProductStock::where('product_id', $this->product->id)->first();
        $this->assertEquals(10, $stock->available_stock, 'Stock must NOT be deducted when placing order');
        $this->assertEquals(0, $stock->sold_count);
    }

    /** @test */
    public function order_confirmation_deducts_stock_and_prevents_duplicate_deduction()
    {
        // Place order
        session()->put('cart', [$this->product->id => 3]);
        $customerPhone = '0775556677';
        $checkout = new Checkout;
        $checkout->mount();
        $checkout->full_name = 'Vimalanathan';
        $checkout->phone = $customerPhone;
        $checkout->address = '12 Kandy Road';
        $checkout->city = 'Chavakachcheri';
        $checkout->payment_method = 'COD';
        $checkout->placeOrder();

        $order = EcommerceOrder::where('customer_phone', $customerPhone)->latest()->first();
        $this->assertNotNull($order);
        $sale = Sale::where('ecommerce_order_id', $order->id)->first();
        $this->assertNotNull($sale);

        // Initial check: stock is 10
        $stock = ProductStock::where('product_id', $this->product->id)->first();
        $this->assertEquals(10, $stock->available_stock);
        $this->assertEquals(0, $stock->sold_count);

        // Confirmation: deducts stock
        $result = $sale->confirmAndDeductStock();
        $this->assertTrue($result, 'confirmAndDeductStock must return true on first call');

        $stock->refresh();
        $this->assertEquals(7, $stock->available_stock, 'Stock must be reduced by ordered quantity (10 - 3 = 7)');
        $this->assertEquals(3, $stock->sold_count);
        $sale->refresh();
        $this->assertEquals('confirm', $sale->status);

        // Duplicate confirmation safeguard: calling confirmAndDeductStock again should do nothing
        $duplicateResult = $sale->confirmAndDeductStock();
        $this->assertFalse($duplicateResult, 'Duplicate confirmation should be prevented');
        $stock->refresh();
        $this->assertEquals(7, $stock->available_stock, 'Stock must not be double deducted');
        $this->assertEquals(3, $stock->sold_count);
    }

    /** @test */
    public function delivery_order_detail_page_loads_and_shows_order_details_and_label_action()
    {
        // Setup a confirmed order
        session()->put('cart', [$this->product->id => 1]);
        $customerPhone = '0773344556';
        $checkout = new Checkout;
        $checkout->mount();
        $checkout->full_name = 'Niroshan Selva';
        $checkout->phone = $customerPhone;
        $checkout->address = '56 Temple Road';
        $checkout->city = 'Nallur';
        $checkout->payment_method = 'COD';
        $checkout->placeOrder();

        $order = EcommerceOrder::where('customer_phone', $customerPhone)->latest()->first();
        $sale = Sale::where('ecommerce_order_id', $order->id)->first();
        $sale->confirmAndDeductStock();

        // Acting as delivery man
        $this->actingAs($this->deliveryUser);

        $response = $this->get(route('delivery.order-detail', $sale->id));
        $response->assertStatus(200);
        $response->assertSee($sale->invoice_number);
        $response->assertSee('Niroshan Selva');
        $response->assertSee('56 Temple Road');
        $response->assertSee('Nallur');
        $response->assertSee('22K Handcrafted Gold Bangle');
        $response->assertSee(route('delivery.print.delivery-label', $sale->id));
    }

    /** @test */
    public function delivery_order_lifecycle_from_confirmation_to_delivered_with_cod()
    {
        // Setup order
        session()->put('cart', [$this->product->id => 2]);
        $customerPhone = '0778899001';
        $checkout = new Checkout;
        $checkout->mount();
        $checkout->full_name = 'Mayuran Sinnathamby';
        $checkout->phone = $customerPhone;
        $checkout->address = '90 Beach Road';
        $checkout->city = 'Point Pedro';
        $checkout->payment_method = 'COD';
        $checkout->placeOrder();

        $order = EcommerceOrder::where('customer_phone', $customerPhone)->latest()->first();
        $sale = Sale::where('ecommerce_order_id', $order->id)->first();

        $this->actingAs($this->deliveryUser);

        // Test Livewire component lifecycle transitions
        Livewire::test(DeliveryOrderDetail::class, ['id' => $sale->id])
            // 1. Confirm order (deducts stock)
            ->call('confirmOrder')
            ->assertSet('sale.status', 'confirm')
            // 2. Mark Packed
            ->call('markPacked')
            ->assertSet('sale.delivery_status', 'packed')
            // 3. Mark In Transit with Tracking Number
            ->set('courierTrackingNumber', 'DMX-JAFFNA-998877')
            ->set('courierMethod', 'Domex')
            ->call('markInTransit')
            ->assertSet('sale.delivery_status', 'in_transit')
            // 4. Mark Delivered
            ->call('markDelivered')
            ->assertSet('sale.delivery_status', 'delivered')
            // 5. Collect COD Cash Payment
            ->set('paymentAmount', $sale->total_amount)
            ->set('paymentMethod', 'cash')
            ->set('paymentNotes', 'Collected cash upon delivery at Point Pedro')
            ->call('recordPayment')
            ->assertSet('showPaymentModal', false);

        // Verify in database
        $sale->refresh();
        $this->assertEquals('delivered', $sale->delivery_status);
        $this->assertEquals(0, $sale->due_amount);
        $this->assertEquals('paid', $sale->payment_status);

        $deliverySale = DeliverySale::where('sale_id', $sale->id)->first();
        $this->assertEquals('Delivered', $deliverySale->status);
        $this->assertEquals('DMX-JAFFNA-998877', $deliverySale->tracking_number);

        // Verify stock deducted accurately
        $stock = ProductStock::where('product_id', $this->product->id)->first();
        $this->assertEquals(8, $stock->available_stock);
        $this->assertEquals(2, $stock->sold_count);
    }

    /** @test */
    public function order_cancellation_or_return_restores_deducted_stock()
    {
        // Setup order & confirm it
        session()->put('cart', [$this->product->id => 2]);
        $customerPhone = '0774433221';
        $checkout = new Checkout;
        $checkout->mount();
        $checkout->full_name = 'Gajan Velupillai';
        $checkout->phone = $customerPhone;
        $checkout->address = '23 Station Road';
        $checkout->city = 'Jaffna';
        $checkout->payment_method = 'COD';
        $checkout->placeOrder();

        $order = EcommerceOrder::where('customer_phone', $customerPhone)->latest()->first();
        $sale = Sale::where('ecommerce_order_id', $order->id)->first();
        $sale->confirmAndDeductStock();

        $stock = ProductStock::where('product_id', $this->product->id)->first();
        $this->assertEquals(8, $stock->available_stock);
        $this->assertEquals(2, $stock->sold_count);

        // Cancel order via DeliveryOrderDetail
        $this->actingAs($this->deliveryUser);

        Livewire::test(DeliveryOrderDetail::class, ['id' => $sale->id])
            ->call('openCancelModal')
            ->set('cancelReason', 'Customer changed mind before dispatch')
            ->call('executeCancelOrReturn');

        // Verify stock restored
        $stock->refresh();
        $this->assertEquals(10, $stock->available_stock, 'Stock must be restored to 10 upon cancellation');
        $this->assertEquals(0, $stock->sold_count);

        $sale->refresh();
        $this->assertEquals('cancelled', $sale->delivery_status);
        $this->assertEquals('cancelled', $sale->status);
    }

    /** @test */
    public function product_detail_multi_image_gallery_loads_properly()
    {
        // Assert all_image_urls includes both main and extra images
        $imageUrls = $this->product->all_image_urls;
        $this->assertCount(4, $imageUrls, 'Should contain main image plus 3 gallery images');

        Livewire::test(ProductDetailComponent::class, ['slug' => $this->product->slug])
            ->assertStatus(200)
            ->assertSet('selectedImageIndex', 0)
            ->call('selectImage', 2)
            ->assertSet('selectedImageIndex', 2)
            ->assertSee('22K Handcrafted Gold Bangle');
    }

    /** @test */
    public function product_image_upload_endpoint_accepts_and_processes_images()
    {
        $file1 = \Illuminate\Http\UploadedFile::fake()->image('craft_angle1.jpg', 600, 600);
        $file2 = \Illuminate\Http\UploadedFile::fake()->image('craft_angle2.png', 600, 600);

        $response = $this->postJson(route('product-image.upload', $this->product->id), [
            'images' => [$file1, $file2],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'uploaded_count' => 2,
        ]);

        $this->product->refresh();
        $this->assertNotEmpty($this->product->images);
    }

    /** @test */
    public function can_upload_more_than_twenty_three_images_for_products()
    {
        // Generate 25 distinct images
        $files = [];
        for ($i = 1; $i <= 25; $i++) {
            $files[] = \Illuminate\Http\UploadedFile::fake()->image("bangle_shot_{$i}.jpg", 200, 200);
        }

        // Upload in batches of 5 (matching client-side batched upload)
        $chunks = array_chunk($files, 5);
        foreach ($chunks as $chunk) {
            $response = $this->postJson(route('product-image.upload', $this->product->id), [
                'images' => $chunk,
            ]);
            $response->assertStatus(200);
            $response->assertJson(['success' => true]);
        }

        $this->product->refresh();
        $allUrls = $this->product->all_image_urls;
        // Total images in product must now be at least 25
        $this->assertGreaterThanOrEqual(25, count($allUrls));

        $lastIndex = count($allUrls) - 1;

        // Verify product detail component can navigate up to the last image and wrap around
        Livewire::test(ProductDetailComponent::class, ['slug' => $this->product->slug])
            ->assertStatus(200)
            ->call('selectImage', 24)
            ->assertSet('selectedImageIndex', 24)
            ->call('selectImage', $lastIndex)
            ->assertSet('selectedImageIndex', $lastIndex)
            ->call('nextImage')
            ->assertSet('selectedImageIndex', 0)
            ->call('prevImage')
            ->assertSet('selectedImageIndex', $lastIndex);
    }
}
