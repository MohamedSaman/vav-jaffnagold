<?php

namespace Tests\Feature;

use App\Livewire\ShopStaff\GoldPlating;
use App\Models\GoldPlatingOrder;
use App\Models\GoldPlatingOrderItem;
use App\Models\GoldPlatingWorker;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class GoldPlatingReadySmsNotificationTest extends TestCase
{
    use DatabaseTransactions;

    protected $shopStaff;

    protected $worker;

    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $userId = DB::table('users')->insertGetId([
            'name' => 'Test Staff',
            'email' => 'staff_sms_'.uniqid().'@example.com',
            'password' => bcrypt('password'),
            'role' => 'staff',
            'contact' => '0771234567',
            'staff_type' => 'shop_staff',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->shopStaff = User::find($userId);

        $this->worker = GoldPlatingWorker::create([
            'name' => 'Craftsman Ramesh',
            'phone' => '0779998888',
            'address' => 'Workshop Jaffna',
            'is_active' => true,
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->actingAs($this->shopStaff);
    }

    public function test_format_gold_plating_ready_message_contains_7_days_and_shop_contact(): void
    {
        $order = GoldPlatingOrder::create([
            'order_code' => 'GP-TEST-SMS-001',
            'user_id' => $this->shopStaff->id,
            'customer_name' => 'Kugan Theva',
            'customer_phone' => '0771234567',
            'service_type' => 'gold_plating',
            'item_description' => 'Kodi chain plating',
            'customer_charge' => 2500,
            'advance_amount' => 1000,
            'balance_amount' => 1500,
            'status' => 'received',
            'received_date' => now(),
            'order_category' => 'customer',
        ]);

        $message = SmsService::formatGoldPlatingReadyMessage($order);

        $this->assertStringContainsString('Kugan Theva', $message);
        $this->assertStringContainsString('GP-TEST-SMS-001', $message);
        $this->assertStringContainsString('READY', $message);
        $this->assertStringContainsString('1,500.00', $message);
        $this->assertStringContainsString('7 days', $message);
        $this->assertStringContainsString('Jaffna Gold', $message);
        $this->assertStringContainsString('0761919650', $message);
    }

    public function test_updating_status_to_ready_triggers_sms_and_updates_ready_sms_sent_at(): void
    {
        Http::fake([
            'https://smslenz.lk/api/send-sms' => Http::response([
                'success' => true,
                'message' => 'SMS queued successfully',
            ], 200),
        ]);

        $order = GoldPlatingOrder::create([
            'order_code' => 'GP-TEST-SMS-002',
            'user_id' => $this->shopStaff->id,
            'customer_name' => 'Sangeetha',
            'customer_phone' => '0778889999',
            'service_type' => 'gold_plating',
            'item_description' => 'Gold ring polishing',
            'customer_charge' => 1200,
            'advance_amount' => 500,
            'balance_amount' => 700,
            'status' => 'in_progress',
            'received_date' => now(),
            'order_category' => 'customer',
        ]);

        Livewire::test(GoldPlating::class)
            ->call('updateStatus', $order->id, 'ready');

        $order->refresh();
        $this->assertEquals('ready', $order->status);
        $this->assertNotNull($order->ready_sms_sent_at);

        Http::assertSent(function ($request) {
            return str_contains($request['message'], '7 days') &&
                   str_contains($request['message'], '0761919650') &&
                   $request['contact'] === '+94778889999';
        });
    }

    public function test_receiving_all_items_triggers_ready_sms(): void
    {
        Http::fake([
            'https://smslenz.lk/api/send-sms' => Http::response([
                'success' => true,
                'message' => 'SMS sent',
            ], 200),
        ]);

        $order = GoldPlatingOrder::create([
            'order_code' => 'GP-TEST-SMS-003',
            'user_id' => $this->shopStaff->id,
            'customer_name' => 'Ravi Kumar',
            'customer_phone' => '0765554444',
            'service_type' => 'gold_plating',
            'item_description' => '2 items plating',
            'customer_charge' => 3000,
            'advance_amount' => 1000,
            'balance_amount' => 2000,
            'status' => 'in_progress',
            'received_date' => now(),
            'order_category' => 'customer',
        ]);

        $item1 = GoldPlatingOrderItem::create([
            'gold_plating_order_id' => $order->id,
            'item_name' => 'Kodi Chain',
            'fee' => 1500,
            'status' => 'pending',
        ]);

        $item2 = GoldPlatingOrderItem::create([
            'gold_plating_order_id' => $order->id,
            'item_name' => 'Bangle',
            'fee' => 1500,
            'status' => 'pending',
        ]);

        Livewire::test(GoldPlating::class)
            ->call('receiveAllItems', $order->id);

        $order->refresh();
        $this->assertEquals('ready', $order->status);
        $this->assertNotNull($order->ready_sms_sent_at);

        Http::assertSent(function ($request) {
            return str_contains($request['message'], '7 days') &&
                   $request['contact'] === '+94765554444';
        });
    }

    public function test_can_send_manual_sms_via_modal(): void
    {
        Http::fake([
            'https://smslenz.lk/api/send-sms' => Http::response([
                'success' => true,
                'message' => 'SMS sent',
            ], 200),
        ]);

        $order = GoldPlatingOrder::create([
            'order_code' => 'GP-TEST-SMS-004',
            'user_id' => $this->shopStaff->id,
            'customer_name' => 'Thulasi',
            'customer_phone' => '0712223333',
            'service_type' => 'gold_plating',
            'item_description' => 'Necklace plating',
            'customer_charge' => 5000,
            'advance_amount' => 5000,
            'balance_amount' => 0,
            'status' => 'ready',
            'received_date' => now(),
            'order_category' => 'customer',
        ]);

        $component = Livewire::test(GoldPlating::class)
            ->call('openSmsModal', $order->id)
            ->assertSet('showSmsModal', true)
            ->assertSet('smsCustomerPhone', '0712223333');

        $this->assertStringContainsString('7 days', $component->get('smsMessageText'));
        $this->assertStringContainsString('0761919650', $component->get('smsMessageText'));

        $component->call('sendSmsNotification')
            ->assertSet('showSmsModal', false);

        $order->refresh();
        $this->assertNotNull($order->ready_sms_sent_at);

        Http::assertSent(function ($request) {
            return str_contains($request['message'], '7 days') &&
                   $request['contact'] === '+94712223333';
        });
    }

    public function test_shop_internal_orders_do_not_send_customer_sms(): void
    {
        Http::fake();

        $order = GoldPlatingOrder::create([
            'order_code' => 'GP-TEST-SHOP-SMS',
            'user_id' => $this->shopStaff->id,
            'customer_name' => 'Showcase Shop Stock',
            'customer_phone' => 'Internal Tag',
            'service_type' => 'gold_plating',
            'item_description' => '10 internal rings',
            'our_cost' => 1500,
            'customer_charge' => 0,
            'balance_amount' => 0,
            'status' => 'in_progress',
            'received_date' => now(),
            'order_category' => 'shop',
        ]);

        Livewire::test(GoldPlating::class)
            ->call('updateStatus', $order->id, 'ready');

        $order->refresh();
        $this->assertEquals('ready', $order->status);
        $this->assertNull($order->ready_sms_sent_at);

        Http::assertNothingSent();
    }
}
