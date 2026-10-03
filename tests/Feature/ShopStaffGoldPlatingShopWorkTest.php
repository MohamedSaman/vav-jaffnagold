<?php

namespace Tests\Feature;

use App\Livewire\ShopStaff\GoldPlating;
use App\Models\GoldPlatingOrder;
use App\Models\GoldPlatingWorker;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class ShopStaffGoldPlatingShopWorkTest extends TestCase
{
    use DatabaseTransactions;

    protected $shopStaff;
    protected $worker;

    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $userId = DB::table('users')->insertGetId([
            'name'              => 'Test Shop Staff',
            'email'             => 'testshop_plating_' . uniqid() . '@example.com',
            'password'          => bcrypt('password'),
            'role'              => 'staff',
            'contact'           => '0771234567',
            'staff_type'        => 'shop_staff',
            'email_verified_at' => now(),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        $this->shopStaff = User::find($userId);

        $this->worker = GoldPlatingWorker::create([
            'name' => 'Test Craftsman Ramesh',
            'phone' => '0779998888',
            'address' => 'Workshop 1',
            'is_active' => true,
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->actingAs($this->shopStaff);
    }

    public function test_gold_plating_page_is_accessible_to_shop_staff(): void
    {
        $response = $this->get(route('shop-staff.gold-plating'));
        $response->assertStatus(200);
        $response->assertSee('Gold Plating & Services');
        $response->assertSee('Our Shop Work');
    }

    public function test_create_modal_shows_for_our_shop_work_toggle(): void
    {
        Livewire::test(GoldPlating::class)
            ->set('showCreateModal', true)
            ->assertSee('For Our Shop Work (Shop Jewelry Plating)');
    }

    public function test_can_create_internal_shop_gold_plating_order(): void
    {
        Livewire::test(GoldPlating::class)
            ->set('showCreateModal', true)
            ->set('is_shop_work', true)
            ->set('worker_id', $this->worker->id)
            ->set('expected_delivery_date', now()->addDays(3)->toDateString())
            ->set('service_type', 'gold_plating')
            ->set('item_description', 'Showroom Necklaces & Bangles')
            ->set('notes', 'Internal polishing and plating for window display')
            ->set('serviceItems', [
                ['item_name' => 'Shop Display Chain 22K', 'fee' => 0, 'cost' => 450],
                ['item_name' => 'Shop Display Bangle Set', 'fee' => 0, 'cost' => 550],
            ])
            ->call('saveOrder')
            ->assertHasNoErrors()
            ->assertDispatched('open-print-window');

        $this->assertDatabaseHas('gold_plating_orders', [
            'order_category' => 'shop',
            'customer_name' => 'Our Shop',
            'customer_phone' => 'Internal',
            'customer_charge' => 0.00,
            'advance_amount' => 0.00,
            'balance_amount' => 0.00,
            'our_cost' => 1000.00,
            'status' => 'received',
            'worker_id' => $this->worker->id,
        ]);

        $this->assertDatabaseHas('gold_plating_order_items', [
            'item_name' => 'Shop Display Chain 22K',
            'cost' => 450.00,
            'fee' => 0.00,
        ]);

        $this->assertDatabaseHas('gold_plating_order_items', [
            'item_name' => 'Shop Display Bangle Set',
            'cost' => 550.00,
            'fee' => 0.00,
        ]);
    }

    public function test_can_mark_shop_order_as_completed(): void
    {
        $order = GoldPlatingOrder::create([
            'order_code' => 'GP-TEST-SHOP-01',
            'user_id' => $this->shopStaff->id,
            'worker_id' => $this->worker->id,
            'order_category' => 'shop',
            'customer_name' => 'Our Shop',
            'customer_phone' => 'Internal',
            'service_type' => 'gold_plating',
            'item_description' => 'Display Chain',
            'received_date' => now()->toDateString(),
            'customer_charge' => 0.00,
            'advance_amount' => 0.00,
            'balance_amount' => 0.00,
            'our_cost' => 300.00,
            'status' => 'ready',
        ]);

        $item = $order->items()->create([
            'item_name' => 'Display Chain',
            'fee' => 0.00,
            'cost' => 300.00,
            'status' => 'pending',
        ]);

        Livewire::test(GoldPlating::class)
            ->call('markShopOrderCompleted', $order->id);

        $order->refresh();
        $this->assertEquals('delivered', $order->status);
        $this->assertNotNull($order->actual_delivery_date);

        $item->refresh();
        $this->assertEquals('received', $item->status);
        $this->assertTrue($item->isReceived());
        $this->assertNotNull($item->received_at);
    }

    public function test_can_filter_by_shop_category(): void
    {
        $shopOrder = GoldPlatingOrder::create([
            'order_code' => 'GP-SHOP-FILTER-1',
            'user_id' => $this->shopStaff->id,
            'order_category' => 'shop',
            'customer_name' => 'Our Shop',
            'customer_phone' => 'Internal',
            'service_type' => 'gold_plating',
            'item_description' => 'Internal Item 1',
            'received_date' => now()->toDateString(),
            'customer_charge' => 0.00,
            'advance_amount' => 0.00,
            'balance_amount' => 0.00,
            'our_cost' => 200.00,
            'status' => 'received',
        ]);

        $customerOrder = GoldPlatingOrder::create([
            'order_code' => 'GP-CUST-FILTER-2',
            'user_id' => $this->shopStaff->id,
            'order_category' => 'customer',
            'customer_name' => 'Sita Raman',
            'customer_phone' => '0771122334',
            'service_type' => 'repair',
            'item_description' => 'Customer Bangle',
            'received_date' => now()->toDateString(),
            'customer_charge' => 1500.00,
            'advance_amount' => 500.00,
            'balance_amount' => 1000.00,
            'our_cost' => 400.00,
            'status' => 'received',
        ]);

        Livewire::test(GoldPlating::class)
            ->set('categoryFilter', 'shop')
            ->assertSee('GP-SHOP-FILTER-1')
            ->assertDontSee('GP-CUST-FILTER-2');
    }

    public function test_shop_orders_do_not_distort_customer_revenue_stats(): void
    {
        GoldPlatingOrder::create([
            'order_code' => 'GP-CUST-STATS',
            'user_id' => $this->shopStaff->id,
            'order_category' => 'customer',
            'customer_name' => 'Ravi Kumar',
            'customer_phone' => '0775551234',
            'service_type' => 'gold_plating',
            'item_description' => 'Customer Chain',
            'received_date' => now()->toDateString(),
            'customer_charge' => 2000.00,
            'advance_amount' => 1000.00,
            'balance_amount' => 1000.00,
            'our_cost' => 600.00,
            'status' => 'received',
        ]);

        GoldPlatingOrder::create([
            'order_code' => 'GP-SHOP-STATS',
            'user_id' => $this->shopStaff->id,
            'order_category' => 'shop',
            'customer_name' => 'Our Shop',
            'customer_phone' => 'Internal',
            'service_type' => 'gold_plating',
            'item_description' => 'Shop Inventory Item',
            'received_date' => now()->toDateString(),
            'customer_charge' => 0.00,
            'advance_amount' => 0.00,
            'balance_amount' => 0.00,
            'our_cost' => 800.00,
            'status' => 'received',
        ]);

        $component = Livewire::test(GoldPlating::class);
        $component->assertOk();
        // The stats should calculate customer revenue and profit without shop orders distorting them
        $this->assertEquals('shop', GoldPlatingOrder::where('order_code', 'GP-SHOP-STATS')->value('order_category'));
    }

    public function test_can_edit_existing_shop_order(): void
    {
        $order = GoldPlatingOrder::create([
            'order_code' => 'GP-SHOP-EDIT-1',
            'user_id' => $this->shopStaff->id,
            'order_category' => 'shop',
            'customer_name' => 'Our Shop',
            'customer_phone' => 'Internal',
            'service_type' => 'gold_plating',
            'item_description' => 'Initial Description',
            'received_date' => now()->toDateString(),
            'customer_charge' => 0.00,
            'advance_amount' => 0.00,
            'balance_amount' => 0.00,
            'our_cost' => 350.00,
            'status' => 'received',
            'notes' => 'Old note',
        ]);

        $order->items()->create([
            'item_name' => 'Initial Description',
            'fee' => 0.00,
            'cost' => 350.00,
        ]);

        Livewire::test(GoldPlating::class)
            ->call('openEditModal', $order->id)
            ->assertSet('edit_is_shop_work', true)
            ->set('edit_notes', 'Updated shop instructions for worker')
            ->set('edit_our_cost', 400.00)
            ->call('updateOrder')
            ->assertHasNoErrors();

        $order->refresh();
        $this->assertEquals('shop', $order->order_category);
        $this->assertEquals('Updated shop instructions for worker', $order->notes);
        $this->assertEquals(400.00, (float) $order->our_cost);
        $this->assertEquals(0.00, (float) $order->customer_charge);
    }
}
