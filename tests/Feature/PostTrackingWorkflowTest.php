<?php

namespace Tests\Feature;

use App\Livewire\DeliveryMan\DeliveryManDashboard;
use App\Models\Customer;
use App\Models\DeliverySale;
use App\Models\PostOrderDetail;
use App\Models\Sale;
use App\Models\User;
use App\Services\PostTrackingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class PostTrackingWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    protected $deliveryUser;

    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $userId = DB::table('users')->insertGetId([
            'name' => 'Test Delivery Man',
            'email' => 'dm_post_'.uniqid().'@example.com',
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
    public function post_service_generates_correct_tracking_url()
    {
        $url = PostTrackingService::getTrackingUrl('RA083556964LK');
        $this->assertEquals('https://bepost.lk/p/Search/', $url);
    }

    /** @test */
    public function post_service_parses_html_correctly()
    {
        $sampleHtml = '
        <html>
            <table class="result-table">
                <thead>
                    <tr>
                        <th>Barcode</th>
                        <th>Acceptance PO</th>
                        <th>Delivery PO</th>
                        <th>Received Date</th>
                        <th>Status</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>RA083556964LK</td>
                        <td>Colombo GPO</td>
                        <td>Jaffna PO</td>
                        <td>2026-09-28 14:30:00</td>
                        <td><span class="status-settled">Settled</span></td>
                        <td>4,500.00</td>
                    </tr>
                </tbody>
            </table>
        </html>';

        $service = new PostTrackingService;
        $res = $service->parseHtml($sampleHtml, 'RA083556964LK');

        $this->assertTrue($res['success']);
        $this->assertTrue($res['found']);
        $this->assertEquals('RA083556964LK', $res['barcode']);
        $this->assertEquals('delivered', $res['normalized_status']);
        $this->assertTrue($res['is_delivered']);
        $this->assertTrue($res['is_final']);
        $this->assertEquals('Jaffna PO', $res['delivery_po']);
        $this->assertEquals('Colombo GPO', $res['acceptance_po']);
        $this->assertEquals('2026-09-28 14:30:00', $res['received_date']);
        $this->assertStringContainsString('Settled', $res['status_detail']);
    }

    /** @test */
    public function post_service_marks_as_delivered_and_records_cod_payment()
    {
        $customer = Customer::create([
            'name' => 'Post Test Customer',
            'phone' => '0779876543',
            'address' => 'Jaffna',
        ]);

        $sale = Sale::create([
            'sale_id' => 'SALE-POST-'.uniqid(),
            'customer_id' => $customer->id,
            'user_id' => $this->deliveryUser->id,
            'delivered_by' => $this->deliveryUser->id,
            'invoice_number' => 'INV-TEST-POST-'.uniqid(),
            'subtotal' => 3500,
            'total_amount' => 3500,
            'due_amount' => 3500,
            'status' => 'pending',
            'delivery_status' => 'in_transit',
            'order_source' => 'whatsapp',
            'payment_status' => 'pending',
        ]);

        $deliverySale = DeliverySale::create([
            'sale_id' => $sale->id,
            'delivery_method' => 'Post',
            'tracking_number' => 'RA999999999LK',
            'status' => 'In Transit',
        ]);

        $trackingData = [
            'success' => true,
            'found' => true,
            'barcode' => 'RA999999999LK',
            'status' => 'Settled',
            'normalized_status' => 'delivered',
            'is_delivered' => true,
            'is_final' => true,
            'delivery_po' => 'Jaffna PO',
            'acceptance_po' => 'Colombo GPO',
            'received_date' => '2026-09-28 15:00:00',
            'cod_amount' => '3500.00',
            'status_detail' => 'Settled - Delivery PO: Jaffna PO - (2026-09-28 15:00:00)',
            'events' => [
                ['date_time' => '2026-09-28 15:00:00', 'status' => 'Settled', 'location' => 'Jaffna PO'],
            ],
            'raw_fields' => [],
        ];

        $service = new PostTrackingService;
        $applied = $service->applyTrackingResultToSale($sale, $deliverySale, $trackingData);

        $this->assertTrue($applied['updated']);
        $this->assertTrue($applied['was_delivered']);
        $this->assertTrue($applied['is_final']);

        $sale->refresh();
        $deliverySale->refresh();

        $this->assertEquals('delivered', $sale->delivery_status);
        $this->assertEquals('pending', $sale->payment_status);
        $this->assertEquals(3500, (float) $sale->due_amount);
        $this->assertEquals('Delivered', $deliverySale->status);

        // Check PostOrderDetail
        $postDetail = PostOrderDetail::where('barcode', 'RA999999999LK')->first();
        $this->assertNotNull($postDetail);
        $this->assertEquals('Settled', $postDetail->status);
        $this->assertEquals('Jaffna PO', $postDetail->delivery_po);
        $this->assertTrue($postDetail->is_final);
    }

    /** @test */
    public function dashboard_can_sync_post_order_live()
    {
        $customer = Customer::create([
            'name' => 'Post Dashboard Customer',
            'phone' => '0771122334',
            'address' => 'Jaffna',
        ]);

        $sale = Sale::create([
            'sale_id' => 'SALE-POST-'.uniqid(),
            'customer_id' => $customer->id,
            'user_id' => $this->deliveryUser->id,
            'delivered_by' => $this->deliveryUser->id,
            'invoice_number' => 'INV-DASH-POST-'.uniqid(),
            'subtotal' => 5000,
            'total_amount' => 5000,
            'due_amount' => 5000,
            'status' => 'pending',
            'delivery_status' => 'in_transit',
            'order_source' => 'whatsapp',
            'payment_status' => 'pending',
        ]);

        $deliverySale = DeliverySale::create([
            'sale_id' => $sale->id,
            'delivery_method' => 'Post',
            'tracking_number' => 'RA123456789LK',
            'status' => 'In Transit',
        ]);

        PostOrderDetail::create([
            'sale_id' => $sale->id,
            'delivery_sale_id' => $deliverySale->id,
            'barcode' => 'RA123456789LK',
            'status' => 'In Transit',
            'normalized_status' => 'in_transit',
            'delivery_po' => 'Jaffna PO',
        ]);

        $this->actingAs($this->deliveryUser);

        Livewire::test(DeliveryManDashboard::class)
            ->call('openPostModal', $sale->id)
            ->assertSet('showPostModal', true)
            ->assertSet('selectedPostSale.id', $sale->id)
            ->call('closePostModal')
            ->assertSet('showPostModal', false)
            ->assertSet('selectedPostSale', null);
    }

    /** @test */
    public function post_order_details_is_marked_final_on_delivered_and_skipped_in_future_syncs()
    {
        $customer = Customer::create([
            'name' => 'Final Customer',
            'phone' => '0770000000',
            'address' => 'Jaffna',
        ]);

        $sale = Sale::create([
            'sale_id' => 'SALE-POST-'.uniqid(),
            'customer_id' => $customer->id,
            'user_id' => $this->deliveryUser->id,
            'delivered_by' => $this->deliveryUser->id,
            'invoice_number' => 'INV-FINAL-POST-'.uniqid(),
            'subtotal' => 1200,
            'total_amount' => 1200,
            'due_amount' => 0,
            'status' => 'confirm',
            'delivery_status' => 'delivered',
            'order_source' => 'whatsapp',
            'payment_status' => 'paid',
        ]);

        $deliverySale = DeliverySale::create([
            'sale_id' => $sale->id,
            'delivery_method' => 'Post',
            'tracking_number' => 'RA888888888LK',
            'status' => 'Delivered',
        ]);

        PostOrderDetail::create([
            'sale_id' => $sale->id,
            'delivery_sale_id' => $deliverySale->id,
            'barcode' => 'RA888888888LK',
            'status' => 'Settled',
            'normalized_status' => 'delivered',
            'is_final' => true,
        ]);

        $service = new PostTrackingService;
        $res = $service->syncMultipleOrders([$sale]);

        $this->assertEquals(0, $res['total']);
        $this->assertStringContainsString('No active Post orders', $res['message']);
    }

    /** @test */
    public function post_date_parser_handles_formats()
    {
        $parsed = PostTrackingService::parsePostDate('2026-09-28 14:30:00');
        $this->assertEquals('2026-09-28 14:30:00', $parsed->format('Y-m-d H:i:s'));

        $fallback = PostTrackingService::parsePostDate('');
        $this->assertInstanceOf(\Carbon\Carbon::class, $fallback);
    }
}
