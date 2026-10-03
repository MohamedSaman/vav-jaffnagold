<?php

namespace Tests\Feature;

use App\Livewire\DeliveryMan\DeliveryManCompletedDeliveries;
use App\Models\Customer;
use App\Models\DeliverySale;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class DeliveryManCompletedDeliveriesTest extends TestCase
{
    use DatabaseTransactions;

    protected $deliveryUser;

    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $userId = DB::table('users')->insertGetId([
            'name' => 'Completed Orders Delivery Man',
            'email' => 'delivery_comp_'.uniqid().'@example.com',
            'password' => bcrypt('password'),
            'role' => 'staff',
            'staff_type' => 'delivery_man',
            'contact' => '0779988776',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->deliveryUser = User::find($userId);
    }

    /** @test */
    public function delivery_man_can_access_completed_page_route()
    {
        $this->actingAs($this->deliveryUser);

        $response = $this->get(route('delivery.completed'));
        $response->assertStatus(200);
        $response->assertSee('Completed Deliveries');
        $response->assertSee('Domex Deliveries');
        $response->assertSee('Post Deliveries');
    }

    /** @test */
    public function completed_page_separates_post_and_domex_deliveries_in_courier_tabs()
    {
        $this->actingAs($this->deliveryUser);

        $customerDomex = Customer::create([
            'name' => 'Domex Recipient Customer',
            'phone' => '0771122334',
            'address' => 'Colombo 03',
        ]);

        $saleDomex = Sale::create([
            'sale_id' => 'S-DMX-'.uniqid(),
            'invoice_number' => 'INV-DMX-'.uniqid(),
            'customer_id' => $customerDomex->id,
            'user_id' => $this->deliveryUser->id,
            'delivered_by' => $this->deliveryUser->id,
            'subtotal' => 4100,
            'total_amount' => 4500,
            'due_amount' => 4500,
            'order_source' => 'delivery_man',
            'delivery_status' => 'delivered',
            'delivered_at' => now(),
            'payment_status' => 'pending',
            'status' => 'confirm',
        ]);

        DeliverySale::create([
            'sale_id' => $saleDomex->id,
            'delivery_method' => 'domex',
            'tracking_number' => 'DMX-TRACK-991',
            'delivery_charge' => 400,
            'status' => 'Delivered',
        ]);

        $customerPost = Customer::create([
            'name' => 'Post Sri Lanka Recipient Customer',
            'phone' => '0775566778',
            'address' => 'Jaffna Town',
        ]);

        $salePost = Sale::create([
            'sale_id' => 'S-POST-'.uniqid(),
            'invoice_number' => 'INV-POST-'.uniqid(),
            'customer_id' => $customerPost->id,
            'user_id' => $this->deliveryUser->id,
            'delivered_by' => $this->deliveryUser->id,
            'subtotal' => 2950,
            'total_amount' => 3200,
            'due_amount' => 0,
            'order_source' => 'delivery_man',
            'delivery_status' => 'delivered',
            'delivered_at' => now(),
            'payment_status' => 'paid',
            'status' => 'confirm',
        ]);

        DeliverySale::create([
            'sale_id' => $salePost->id,
            'delivery_method' => 'post',
            'tracking_number' => 'SLPOST-TRACK-112',
            'delivery_charge' => 250,
            'status' => 'Delivered',
        ]);

        // In 'all' tab: both orders should be visible
        Livewire::test(DeliveryManCompletedDeliveries::class)
            ->set('courierTab', 'all')
            ->assertSee($customerDomex->name)
            ->assertSee('DMX-TRACK-991')
            ->assertSee($customerPost->name)
            ->assertSee('SLPOST-TRACK-112');

        // In 'domex' tab: only Domex order should be visible
        Livewire::test(DeliveryManCompletedDeliveries::class)
            ->set('courierTab', 'domex')
            ->assertSee($customerDomex->name)
            ->assertSee('DMX-TRACK-991')
            ->assertDontSee($customerPost->name)
            ->assertDontSee('SLPOST-TRACK-112');

        // In 'post' tab: only Post order should be visible
        Livewire::test(DeliveryManCompletedDeliveries::class)
            ->set('courierTab', 'post')
            ->assertSee($customerPost->name)
            ->assertSee('SLPOST-TRACK-112')
            ->assertDontSee($customerDomex->name)
            ->assertDontSee('DMX-TRACK-991');
    }

    /** @test */
    public function completed_page_filters_by_today_and_date_range()
    {
        $this->actingAs($this->deliveryUser);

        $customerToday = Customer::create([
            'name' => 'Delivered Today Customer',
            'phone' => '0779911223',
            'address' => 'Nallur',
        ]);

        $saleToday = Sale::create([
            'sale_id' => 'S-TDY-'.uniqid(),
            'invoice_number' => 'INV-TDY-'.uniqid(),
            'customer_id' => $customerToday->id,
            'user_id' => $this->deliveryUser->id,
            'delivered_by' => $this->deliveryUser->id,
            'subtotal' => 4500,
            'total_amount' => 5000,
            'due_amount' => 5000,
            'order_source' => 'delivery_man',
            'delivery_status' => 'delivered',
            'delivered_at' => now(),
            'payment_status' => 'pending',
            'status' => 'confirm',
        ]);

        DeliverySale::create([
            'sale_id' => $saleToday->id,
            'delivery_method' => 'domex',
            'tracking_number' => 'DMX-TODAY-001',
            'status' => 'Delivered',
        ]);

        $customerOld = Customer::create([
            'name' => 'Delivered Last Week Customer',
            'phone' => '0778844332',
            'address' => 'Chavakachcheri',
        ]);

        $oldDate = Carbon::now()->subDays(10);
        $saleOld = Sale::create([
            'sale_id' => 'S-OLD-'.uniqid(),
            'invoice_number' => 'INV-OLD-'.uniqid(),
            'customer_id' => $customerOld->id,
            'user_id' => $this->deliveryUser->id,
            'delivered_by' => $this->deliveryUser->id,
            'subtotal' => 2500,
            'total_amount' => 2800,
            'due_amount' => 0,
            'order_source' => 'delivery_man',
            'delivery_status' => 'delivered',
            'delivered_at' => $oldDate,
            'payment_status' => 'paid',
            'status' => 'confirm',
            'created_at' => $oldDate,
            'updated_at' => $oldDate,
        ]);

        DeliverySale::create([
            'sale_id' => $saleOld->id,
            'delivery_method' => 'post',
            'tracking_number' => 'POST-OLD-002',
            'status' => 'Delivered',
            'created_at' => $oldDate,
            'updated_at' => $oldDate,
        ]);

        // When dateFilter is 'today': sees today customer, does not see old customer
        Livewire::test(DeliveryManCompletedDeliveries::class)
            ->call('setDateFilter', 'today')
            ->assertSee($customerToday->name)
            ->assertSee('DMX-TODAY-001')
            ->assertDontSee($customerOld->name)
            ->assertDontSee('POST-OLD-002');

        // When custom date range includes old date:
        Livewire::test(DeliveryManCompletedDeliveries::class)
            ->set('dateFrom', $oldDate->copy()->subDay()->toDateString())
            ->set('dateTo', $oldDate->copy()->addDay()->toDateString())
            ->call('applyDateRange')
            ->assertSee($customerOld->name)
            ->assertSee('POST-OLD-002')
            ->assertDontSee($customerToday->name);
    }

    /** @test */
    public function delivery_man_can_mark_order_as_completed()
    {
        $this->actingAs($this->deliveryUser);

        $customer = Customer::create([
            'name' => 'To Complete Order Customer',
            'phone' => '0772244668',
            'address' => 'Point Pedro',
        ]);

        $sale = Sale::create([
            'sale_id' => 'S-COMP-'.uniqid(),
            'invoice_number' => 'INV-COMP-'.uniqid(),
            'customer_id' => $customer->id,
            'user_id' => $this->deliveryUser->id,
            'delivered_by' => $this->deliveryUser->id,
            'subtotal' => 1500,
            'total_amount' => 1800,
            'due_amount' => 1800,
            'order_source' => 'delivery_man',
            'delivery_status' => 'in_transit',
            'payment_status' => 'pending',
            'status' => 'confirm',
        ]);

        $deliverySale = DeliverySale::create([
            'sale_id' => $sale->id,
            'delivery_method' => 'domex',
            'tracking_number' => 'DMX-COMP-888',
            'status' => 'In Transit',
        ]);

        Livewire::test(DeliveryManCompletedDeliveries::class)
            ->call('markAsCompleted', $sale->id)
            ->assertDispatched('show-toast');

        $this->assertEquals('completed', $sale->fresh()->delivery_status);
        $this->assertNotNull($sale->fresh()->delivered_at);
        $this->assertEquals('Completed', $deliverySale->fresh()->status);
    }

    /** @test */
    public function delivery_man_can_collect_cod_payment_for_completed_order()
    {
        $this->actingAs($this->deliveryUser);

        $customer = Customer::create([
            'name' => 'COD Completed Customer',
            'phone' => '0773355779',
            'address' => 'Kopay',
        ]);

        $sale = Sale::create([
            'sale_id' => 'S-COD-'.uniqid(),
            'invoice_number' => 'INV-COD-'.uniqid(),
            'customer_id' => $customer->id,
            'user_id' => $this->deliveryUser->id,
            'delivered_by' => $this->deliveryUser->id,
            'subtotal' => 5500,
            'total_amount' => 6000,
            'due_amount' => 6000,
            'order_source' => 'delivery_man',
            'delivery_status' => 'delivered',
            'delivered_at' => now(),
            'payment_status' => 'pending',
            'status' => 'confirm',
        ]);

        $deliverySale = DeliverySale::create([
            'sale_id' => $sale->id,
            'delivery_method' => 'domex',
            'tracking_number' => 'DMX-COD-777',
            'status' => 'Delivered',
        ]);

        Livewire::test(DeliveryManCompletedDeliveries::class)
            ->call('openPaymentModal', $sale->id)
            ->set('paymentAmount', 6000)
            ->set('paymentMethod', 'cash')
            ->call('savePayment')
            ->assertDispatched('show-toast');

        $freshSale = $sale->fresh();
        $this->assertEquals(0, (float) $freshSale->due_amount);
        $this->assertEquals('paid', $freshSale->payment_status);
        $this->assertEquals('completed', $freshSale->delivery_status);

        $this->assertDatabaseHas('payments', [
            'sale_id' => $sale->id,
            'amount' => 6000,
            'payment_method' => 'cash',
        ]);
    }

    /** @test */
    public function completed_page_filters_by_delivered_awaiting_payment_and_completed_status()
    {
        $this->actingAs($this->deliveryUser);

        $customerDelivered = Customer::create([
            'name' => 'Awaiting Courier Payment Customer',
            'phone' => '0771239999',
            'address' => 'Jaffna',
        ]);

        $saleDelivered = Sale::create([
            'sale_id' => 'S-AWAIT-'.uniqid(),
            'invoice_number' => 'INV-AWAIT-'.uniqid(),
            'customer_id' => $customerDelivered->id,
            'user_id' => $this->deliveryUser->id,
            'delivered_by' => $this->deliveryUser->id,
            'subtotal' => 2000,
            'total_amount' => 2400,
            'due_amount' => 2400,
            'order_source' => 'delivery_man',
            'delivery_status' => 'delivered',
            'delivered_at' => now(),
            'payment_status' => 'pending',
            'status' => 'confirm',
        ]);

        DeliverySale::create([
            'sale_id' => $saleDelivered->id,
            'delivery_method' => 'domex',
            'tracking_number' => 'DMX-AWAIT-1',
            'status' => 'Delivered',
        ]);

        $customerCompleted = Customer::create([
            'name' => 'Fully Finished Completed Customer',
            'phone' => '0779870000',
            'address' => 'Nallur',
        ]);

        $saleCompleted = Sale::create([
            'sale_id' => 'S-DONE-'.uniqid(),
            'invoice_number' => 'INV-DONE-'.uniqid(),
            'customer_id' => $customerCompleted->id,
            'user_id' => $this->deliveryUser->id,
            'delivered_by' => $this->deliveryUser->id,
            'subtotal' => 3000,
            'total_amount' => 3500,
            'due_amount' => 0,
            'order_source' => 'delivery_man',
            'delivery_status' => 'completed',
            'delivered_at' => now(),
            'payment_status' => 'paid',
            'status' => 'confirm',
        ]);

        DeliverySale::create([
            'sale_id' => $saleCompleted->id,
            'delivery_method' => 'post',
            'tracking_number' => 'POST-DONE-1',
            'status' => 'Completed',
        ]);

        // When statusFilter is 'delivered' (awaiting payment): sees delivered, does not see completed
        Livewire::test(DeliveryManCompletedDeliveries::class)
            ->call('setStatusFilter', 'delivered')
            ->assertSee($customerDelivered->name)
            ->assertDontSee($customerCompleted->name);

        // When statusFilter is 'completed' (fully settled): sees completed, does not see delivered
        Livewire::test(DeliveryManCompletedDeliveries::class)
            ->call('setStatusFilter', 'completed')
            ->assertSee($customerCompleted->name)
            ->assertDontSee($customerDelivered->name);
    }

    /** @test */
    public function completed_orders_do_not_show_on_delivery_dashboard_page()
    {
        $this->actingAs($this->deliveryUser);

        $customerCompleted = Customer::create([
            'name' => 'Secret Completed Customer '.uniqid(),
            'phone' => '0779900112',
            'address' => 'Chundikuli',
        ]);

        $saleCompleted = Sale::create([
            'sale_id' => 'S-COMPL-'.uniqid(),
            'invoice_number' => 'INV-COMPL-'.uniqid(),
            'customer_id' => $customerCompleted->id,
            'user_id' => $this->deliveryUser->id,
            'delivered_by' => $this->deliveryUser->id,
            'subtotal' => 2000,
            'total_amount' => 2500,
            'due_amount' => 0,
            'order_source' => 'delivery_man',
            'delivery_status' => 'completed',
            'delivered_at' => now(),
            'payment_status' => 'paid',
            'status' => 'confirm',
        ]);

        DeliverySale::create([
            'sale_id' => $saleCompleted->id,
            'delivery_method' => 'domex',
            'tracking_number' => 'DMX-COMP-TEST-1',
            'status' => 'Completed',
        ]);

        $customerDelivered = Customer::create([
            'name' => 'Active Delivered Customer '.uniqid(),
            'phone' => '0779900334',
            'address' => 'Jaffna',
        ]);

        $saleDelivered = Sale::create([
            'sale_id' => 'S-DELIV-'.uniqid(),
            'invoice_number' => 'INV-DELIV-'.uniqid(),
            'customer_id' => $customerDelivered->id,
            'user_id' => $this->deliveryUser->id,
            'delivered_by' => $this->deliveryUser->id,
            'subtotal' => 1800,
            'total_amount' => 2200,
            'due_amount' => 2200,
            'order_source' => 'delivery_man',
            'delivery_status' => 'delivered',
            'delivered_at' => now(),
            'payment_status' => 'pending',
            'status' => 'confirm',
        ]);

        DeliverySale::create([
            'sale_id' => $saleDelivered->id,
            'delivery_method' => 'post',
            'tracking_number' => 'POST-DELIV-TEST-2',
            'status' => 'Delivered',
        ]);

        // On the Delivery Dashboard: completed order MUST NOT show, active delivered order MUST show
        $dashboardResponse = $this->get(route('delivery.dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee($customerDelivered->name);
        $dashboardResponse->assertDontSee($customerCompleted->name);

        // On the Completed Orders Page: completed order MUST show
        $completedResponse = $this->get(route('delivery.completed'));
        $completedResponse->assertStatus(200);
        $completedResponse->assertSee($customerCompleted->name);
    }

    /** @test */
    public function completed_page_displays_bottom_total_amount_delivery_charge_and_receivable_amount()
    {
        $this->actingAs($this->deliveryUser);

        $customer = Customer::create([
            'name' => 'Bottom Summary Customer '.uniqid(),
            'phone' => '0778811223',
            'address' => 'Nallur',
        ]);

        $sale = Sale::create([
            'sale_id' => 'S-SUM-'.uniqid(),
            'invoice_number' => 'INV-SUM-'.uniqid(),
            'customer_id' => $customer->id,
            'user_id' => $this->deliveryUser->id,
            'delivered_by' => $this->deliveryUser->id,
            'subtotal' => 1200,
            'total_amount' => 1650, // 1200 + 450
            'due_amount' => 0,
            'order_source' => 'delivery_man',
            'delivery_status' => 'completed',
            'delivered_at' => now(),
            'payment_status' => 'paid',
            'status' => 'confirm',
        ]);

        DeliverySale::create([
            'sale_id' => $sale->id,
            'delivery_method' => 'Domex',
            'delivery_charge' => 450,
            'tracking_number' => 'DMX-SUM-1',
            'status' => 'Completed',
        ]);

        Livewire::test(DeliveryManCompletedDeliveries::class)
            ->assertSee('Receivable Amount')
            ->assertSee('Delivery Charge')
            ->assertSee('Total Amount')
            ->assertSee('1,650.00') // Total Amount
            ->assertSee('450.00')   // Delivery Charge
            ->assertSee('1,200.00'); // Receivable Amount (1650 - 450)
    }
}
