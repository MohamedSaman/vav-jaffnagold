<?php

namespace Tests\Feature;

use App\Livewire\DeliveryMan\DeliveryManDashboard;
use App\Models\DeliverySale;
use App\Models\Sale;
use App\Models\User;
use App\Services\DomexTrackingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class DomexTrackingWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    protected $deliveryUser;

    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $userId = DB::table('users')->insertGetId([
            'name' => 'Test Delivery Man',
            'email' => 'dm_domex_'.uniqid().'@example.com',
            'password' => bcrypt('password'),
            'role' => 'staff',
            'staff_type' => 'delivery_man',
            'contact' => '0771234567',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->deliveryUser = User::find($userId);
    }

    /** @test */
    public function domex_service_generates_correct_tracking_url()
    {
        $url = DomexTrackingService::getTrackingUrl('EC707069102');
        $this->assertEquals('https://domex.lk/Order-Details.php?wbno=EC707069102', $url);
    }

    /** @test */
    public function domex_service_parses_html_correctly()
    {
        $sampleHtml = '
        <html>
            <td id="WayBillNumber">EC707069102</td>
            <td id="DistinationCity">Trincomalee</td>
            <td id="ReceiverName">sutharshan mer dilani</td>
            <td id="ReceiverPhone">0759273160</td>
            <td id="ispaid">Yes</td>
            <td id="deliveredBy"></td>
            <td id="deliveredTo"></td>
            <tbody id="tracking-table-body">
                <tr>
                    <td>2026-09-19 @ (1:08 pm)</td>
                    <td>Parcel Collected By Jaffna</td>
                </tr>
                <tr>
                    <td>2026-09-22 @ (9:48 am)</td>
                    <td>Out For Delivery By Trincomalee</td>
                </tr>
            </tbody>
        </html>';

        $service = new DomexTrackingService;
        $res = $service->parseHtml($sampleHtml, 'EC707069102');

        $this->assertTrue($res['success']);
        $this->assertTrue($res['found']);
        $this->assertEquals('EC707069102', $res['waybill']);
        $this->assertEquals('in_transit', $res['normalized_status']);
        $this->assertStringContainsString('Out For Delivery By Trincomalee', $res['status_detail']);
        $this->assertEquals('Trincomalee', $res['destination_city']);
        $this->assertCount(2, $res['events']);
    }

    /** @test */
    public function domex_service_marks_as_delivered_when_delivered_status_present()
    {
        $sampleDeliveredHtml = '
        <html>
            <td id="WayBillNumber">EC707069999</td>
            <td id="deliveredBy">Trincomalee Driver</td>
            <td id="deliveredTo">Customer Handover</td>
            <tbody id="tracking-table-body">
                <tr>
                    <td>2026-09-22 @ (2:00 pm)</td>
                    <td>Delivered To Customer By Trincomalee</td>
                </tr>
            </tbody>
        </html>';

        $service = new DomexTrackingService;
        $res = $service->parseHtml($sampleDeliveredHtml, 'EC707069999');

        $this->assertTrue($res['is_delivered']);
        $this->assertEquals('delivered', $res['normalized_status']);
    }

    /** @test */
    public function domex_service_marks_as_delivered_when_cash_received_status_present()
    {
        $sampleCashReceivedHtml = '
        <html>
            <td id="WayBillNumber">EC707069094</td>
            <td id="ispaid">Yes</td>
            <tbody id="tracking-table-body">
                <tr>
                    <td>2026-09-21 @ (5:10 pm)</td>
                    <td>Delivered By Hatton</td>
                </tr>
                <tr>
                    <td>2026-09-21 @ (5:10 pm)</td>
                    <td>POD Submitted By Hatton</td>
                </tr>
                <tr>
                    <td>2026-09-22 @ (9:36 am)</td>
                    <td>Cash Received By Hatton</td>
                </tr>
            </tbody>
        </html>';

        $service = new DomexTrackingService;
        $res = $service->parseHtml($sampleCashReceivedHtml, 'EC707069094');

        $this->assertTrue($res['is_delivered']);
        $this->assertEquals('delivered', $res['normalized_status']);
    }

    /** @test */
    public function dashboard_can_sync_domex_order_live()
    {
        $this->actingAs($this->deliveryUser);

        // Fake Domex endpoint
        Http::fake([
            'https://domex.lk/Order-Details.php?wbno=EC707069102' => Http::response('
                <html>
                    <td id="WayBillNumber">EC707069102</td>
                    <td id="DistinationCity">Trincomalee</td>
                    <td id="ispaid">Yes</td>
                    <td id="deliveredBy"></td>
                    <td id="deliveredTo"></td>
                    <tbody id="tracking-table-body">
                        <tr>
                            <td>2026-09-22 @ (9:48 am)</td>
                            <td>Out For Delivery By Trincomalee</td>
                        </tr>
                    </tbody>
                </html>
            ', 200),
        ]);

        $sale = Sale::create([
            'sale_id' => 'SALE-TEST-DMX-1',
            'invoice_number' => 'INV-DMX-1',
            'subtotal' => 3000,
            'total_amount' => 3450,
            'due_amount' => 3450,
            'payment_status' => 'pending',
            'delivery_status' => 'pending',
            'delivered_by' => $this->deliveryUser->id,
            'user_id' => $this->deliveryUser->id,
            'order_source' => 'whatsapp',
        ]);

        $delSale = DeliverySale::create([
            'sale_id' => $sale->id,
            'delivery_method' => 'Domex',
            'tracking_number' => 'EC707069102',
            'status' => 'Pending',
        ]);

        Livewire::test(DeliveryManDashboard::class)
            ->call('syncSingleDomex', $sale->id)
            ->assertDispatched('show-toast');

        $sale->refresh();
        $delSale->refresh();

        $this->assertEquals('in_transit', $sale->delivery_status);
        $this->assertEquals('In Transit', $delSale->status);
        $this->assertStringContainsString('Out For Delivery By Trincomalee', $delSale->courier_status_detail);
        $this->assertNotNull($delSale->courier_last_synced_at);
        $this->assertNotEmpty($delSale->courier_tracking_history);

        // Also assert domex_order_details record was created
        $this->assertDatabaseHas('domex_order_details', [
            'waybill_number' => 'EC707069102',
            'sale_id' => $sale->id,
            'destination_city' => 'Trincomalee',
            'is_paid' => 'Yes',
            'is_final' => false,
        ]);
    }

    /** @test */
    public function domex_order_details_is_marked_final_on_delivered_and_skipped_in_future_sync()
    {
        $this->actingAs($this->deliveryUser);

        Http::fake([
            'https://domex.lk/Order-Details.php?wbno=EC707069888' => Http::response('
                <html>
                    <td id="WayBillNumber">EC707069888</td>
                    <td id="pickupDate">2026-09-19</td>
                    <td id="paymentMethod">Cash</td>
                    <td id="itemType">Parcel</td>
                    <td id="ispaid">Yes</td>
                    <td id="no_of_peace">1</td>
                    <td id="weight">150 (g)</td>
                    <td id="senderName">JAFFNA GOLD COVERING</td>
                    <td id="DistinationCity">Colombo</td>
                    <td id="ReceiverName">John Doe</td>
                    <td id="ReceiverPhone">0779998888</td>
                    <td id="deliveredBy">Colombo Driver</td>
                    <td id="deliveredTo">John Doe Handover</td>
                    <tbody id="tracking-table-body">
                        <tr>
                            <td>2026-09-22 @ (1:30 pm)</td>
                            <td>Delivered To Customer By Colombo</td>
                        </tr>
                    </tbody>
                </html>
            ', 200),
        ]);

        $sale = Sale::create([
            'sale_id' => 'SALE-TEST-DMX-2',
            'invoice_number' => 'INV-DMX-2',
            'subtotal' => 5000,
            'total_amount' => 5450,
            'due_amount' => 5450,
            'payment_status' => 'pending',
            'delivery_status' => 'in_transit',
            'delivered_by' => $this->deliveryUser->id,
            'user_id' => $this->deliveryUser->id,
            'order_source' => 'whatsapp',
        ]);

        $delSale = DeliverySale::create([
            'sale_id' => $sale->id,
            'delivery_method' => 'Domex',
            'tracking_number' => 'EC707069888',
            'status' => 'In Transit',
        ]);

        $service = new DomexTrackingService;
        $res = $service->syncOrder($sale);

        $this->assertTrue($res['updated']);
        $this->assertTrue($res['was_delivered']);
        $this->assertTrue($res['is_final']);

        // Check domex_order_details in DB
        $detail = \App\Models\DomexOrderDetail::where('waybill_number', 'EC707069888')->first();
        $this->assertNotNull($detail);
        $this->assertEquals('delivered', $detail->normalized_status);
        $this->assertTrue($detail->is_final);
        $this->assertEquals('John Doe', $detail->receiver_name);
        $this->assertEquals('Colombo', $detail->destination_city);
        $this->assertEquals('John Doe Handover', $detail->delivered_to);
        $this->assertEquals('150 (g)', $detail->weight);
        $this->assertCount(1, $detail->tracking_history);

        // Verify that order is delivered but awaiting manual courier payment settlement
        $sale->refresh();
        $this->assertEquals('delivered', $sale->delivery_status);
        $this->assertEquals(5450, (float) $sale->due_amount);
        $this->assertEquals('pending', $sale->payment_status);
        $this->assertDatabaseMissing('payments', [
            'sale_id' => $sale->id,
        ]);

        $syncResult = $service->syncMultipleOrders(collect([$sale]));
        $this->assertEquals(0, $syncResult['total']);
    }

    /** @test */
    public function dashboard_can_open_domex_modal_with_full_details()
    {
        $this->actingAs($this->deliveryUser);

        $sale = Sale::create([
            'sale_id' => 'SALE-TEST-DMX-3',
            'invoice_number' => 'INV-DMX-3',
            'subtotal' => 2000,
            'total_amount' => 2450,
            'due_amount' => 2450,
            'payment_status' => 'pending',
            'delivery_status' => 'in_transit',
            'delivered_by' => $this->deliveryUser->id,
            'user_id' => $this->deliveryUser->id,
            'order_source' => 'ecommerce',
        ]);

        $delSale = DeliverySale::create([
            'sale_id' => $sale->id,
            'delivery_method' => 'Domex',
            'tracking_number' => 'EC707069777',
            'status' => 'In Transit',
        ]);

        $domexDetail = \App\Models\DomexOrderDetail::create([
            'sale_id' => $sale->id,
            'delivery_sale_id' => $delSale->id,
            'waybill_number' => 'EC707069777',
            'current_status' => 'In Transit to Hub',
            'normalized_status' => 'in_transit',
            'is_final' => false,
            'receiver_name' => 'Alice Smith',
            'destination_city' => 'Kandy',
            'tracking_history' => [
                ['date_time' => '2026-09-21 10:00 AM', 'status' => 'Picked up from Jaffna'],
            ],
        ]);

        Livewire::test(DeliveryManDashboard::class)
            ->call('openDomexModal', $sale->id)
            ->assertSet('showDomexModal', true)
            ->assertSet('selectedDomexSale.id', $sale->id)
            ->assertSee('EC707069777')
            ->assertSee('Alice Smith')
            ->assertSee('Kandy')
            ->assertSee('Picked up from Jaffna')
            ->call('closeDomexModal')
            ->assertSet('showDomexModal', false);
    }

    /** @test */
    public function domex_date_parser_handles_special_characters_and_formats()
    {
        // Domex standard date format: '2026-09-19 @ (1:08 pm)'
        $parsed = DomexTrackingService::parseDomexDate('2026-09-19 @ (1:08 pm)');
        $this->assertEquals('2026-09-19 13:08:00', $parsed->toDateTimeString());

        // Empty string fallback
        $fallback = DomexTrackingService::parseDomexDate('');
        $this->assertNotNull($fallback);
    }
}
