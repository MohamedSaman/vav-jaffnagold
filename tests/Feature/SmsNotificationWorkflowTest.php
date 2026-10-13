<?php

namespace Tests\Feature;

use App\Livewire\DeliveryMan\DeliveryOrderDetail;
use App\Models\BrandList;
use App\Models\CategoryList;
use App\Models\Customer;
use App\Models\ProductDetail;
use App\Models\ProductPrice;
use App\Models\ProductStock;
use App\Models\ProductSupplier;
use App\Models\Sale;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class SmsNotificationWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    protected $deliveryUser;

    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $this->deliveryUser = User::firstOrCreate(
            ['email' => 'kugan_delivery_sms@jaffnagold.lk'],
            ['name' => 'Kugan Delivery', 'contact' => '0770000000', 'role' => 'delivery_man', 'password' => bcrypt('password123')]
        );

        $brand = BrandList::firstOrCreate(['brand_name' => 'Jaffna Gold Traditional']);
        $cat = CategoryList::firstOrCreate(['category_name' => 'Bangles']);
        $supplier = ProductSupplier::firstOrCreate(
            ['name' => 'Jaffna Master Crafts'],
            ['contact' => '0212222222', 'email' => 'crafts@jaffnagold.lk', 'address' => 'Main Street, Jaffna']
        );

        $this->product = ProductDetail::create([
            'name' => '22K Handcrafted Gold Bangle',
            'code' => 'JGB-'.uniqid(),
            'barcode' => 'BC'.rand(100000, 999999),
            'slug' => '22k-handcrafted-gold-bangle-'.uniqid(),
            'category_id' => $cat->id,
            'brand_id' => $brand->id,
            'supplier_id' => $supplier->id,
            'status' => 'active',
            'website_live' => true,
        ]);

        ProductPrice::create([
            'product_id' => $this->product->id,
            'supplier_price' => 220000.00,
            'retail_price' => 250000.00,
            'selling_price' => 250000.00,
            'cost_price' => 220000.00,
        ]);

        ProductStock::create([
            'product_id' => $this->product->id,
            'available_stock' => 10,
            'damage_stock' => 0,
            'total_stock' => 10,
            'sold_count' => 0,
        ]);
    }

    /** @test */
    public function phone_number_normalization_handles_sri_lankan_formats()
    {
        $this->assertEquals('+94771234567', SmsService::normalizePhoneNumber('0771234567'));
        $this->assertEquals('+94771234567', SmsService::normalizePhoneNumber('077-123-4567'));
        $this->assertEquals('+94771234567', SmsService::normalizePhoneNumber('077 123 4567'));
        $this->assertEquals('+94771234567', SmsService::normalizePhoneNumber('+94771234567'));
        $this->assertEquals('+94771234567', SmsService::normalizePhoneNumber('94771234567'));
        $this->assertEquals('+94712345678', SmsService::normalizePhoneNumber('0712345678'));
        $this->assertEquals('', SmsService::normalizePhoneNumber(''));
        $this->assertEquals('', SmsService::normalizePhoneNumber(null));
    }

    /** @test */
    public function sms_send_dispatches_http_request_with_correct_smslenz_payload()
    {
        Http::fake([
            'https://smslenz.lk/api/send-sms' => Http::response([
                'success' => true,
                'message' => 'SMS queued successfully',
                'data' => ['campaign_id' => '12345'],
            ], 200),
        ]);

        $result = SmsService::sendSms('0771234567', 'Test message from Jaffna Gold');

        $this->assertTrue($result['success']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://smslenz.lk/api/send-sms'
                && $request['contact'] === '+94771234567'
                && $request['user_id'] === '1644'
                && $request['api_key'] === '9b60385e-e599-49f3-a30b-fee08a8aa52a'
                && $request['sender_id'] === config('sms.sender_id')
                && $request['message'] === 'Test message from Jaffna Gold';
        });
    }

    /** @test */
    public function delivery_order_detail_sends_sms_notifications_on_confirmation_and_transit_and_delivery()
    {
        Http::fake([
            'https://smslenz.lk/api/send-sms' => Http::response([
                'success' => true,
                'message' => 'SMS sent',
            ], 200),
        ]);

        $customer = Customer::create([
            'name' => 'Ananthy Sasikaran',
            'phone' => '0771122334',
            'type' => 'retail',
            'address' => '12 Hospital Road, Jaffna',
        ]);

        $sale = Sale::create([
            'sale_id' => Sale::generateSaleId(),
            'invoice_number' => Sale::generateInvoiceNumber(),
            'customer_id' => $customer->id,
            'walking_customer_name' => 'Ananthy Sasikaran',
            'walking_customer_phone' => '0771122334',
            'customer_type' => 'retail',
            'subtotal' => 1000,
            'total_amount' => 1000,
            'due_amount' => 1000,
            'payment_type' => 'partial',
            'payment_status' => 'pending',
            'status' => 'pending',
            'sale_type' => 'staff',
            'order_source' => 'whatsapp',
            'user_id' => $this->deliveryUser->id,
            'delivery_status' => 'pending',
        ]);

        \App\Models\DeliverySale::create([
            'sale_id' => $sale->id,
            'delivery_method' => 'Domex',
            'payment_method' => 'Cash on Delivery',
            'delivery_charge' => 350,
            'status' => 'Processing',
            'customer_details' => "Customer: Ananthy Sasikaran\nPhone: 0771122334\nAddress: 12 Hospital Road, Jaffna",
        ]);

        $this->actingAs($this->deliveryUser);

        // 1. Confirm Order via DeliveryOrderDetail
        Livewire::test(DeliveryOrderDetail::class, ['id' => $sale->id])
            ->call('confirmOrder');

        Http::assertSent(function ($request) use ($sale) {
            return $request['contact'] === '+94771122334'
                && str_contains($request['message'], $sale->invoice_number)
                && str_contains($request['message'], 'CONFIRMED');
        });

        // 2. Mark In Transit with Courier Tracking
        Livewire::test(DeliveryOrderDetail::class, ['id' => $sale->id])
            ->set('courierMethod', 'Domex')
            ->set('courierTrackingNumber', 'DMX-778899')
            ->call('markInTransit');

        Http::assertSent(function ($request) {
            return $request['contact'] === '+94771122334'
                && str_contains($request['message'], 'OUT FOR DELIVERY')
                && str_contains($request['message'], 'Domex')
                && str_contains($request['message'], 'DMX-778899');
        });

        // 3. Mark Delivered
        Livewire::test(DeliveryOrderDetail::class, ['id' => $sale->id])
            ->call('markDelivered');

        Http::assertSent(function ($request) {
            return $request['contact'] === '+94771122334'
                && str_contains($request['message'], 'DELIVERED');
        });

        // 4. Custom Manual SMS
        Livewire::test(DeliveryOrderDetail::class, ['id' => $sale->id])
            ->set('smsCustomMessage', 'Hello Ananthy, rider is 5 mins away.')
            ->call('sendCustomSms');

        Http::assertSent(function ($request) {
            return $request['contact'] === '+94771122334'
                && $request['message'] === 'Hello Ananthy, rider is 5 mins away.';
        });
    }

    /** @test */
    public function smslenz_account_status_check_returns_valid_structure()
    {
        $status = SmsService::checkAccountStatus();

        $this->assertIsArray($status);
        $this->assertTrue($status['success'] ?? false);
        $this->assertArrayHasKey('data', $status);
        $this->assertEquals('active', $status['data']['status'] ?? '');
    }
}
