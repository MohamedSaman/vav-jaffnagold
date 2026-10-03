<?php

namespace Tests\Feature;

use App\Livewire\DeliveryMan\DomexRemittanceImport;
use App\Models\Customer;
use App\Models\DeliverySale;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class DomexRemittanceImportTest extends TestCase
{
    use DatabaseTransactions;

    protected $deliveryUser;

    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $userId = DB::table('users')->insertGetId([
            'name' => 'Domex Import Delivery Man',
            'email' => 'domex_import_'.uniqid().'@example.com',
            'password' => bcrypt('password'),
            'role' => 'staff',
            'staff_type' => 'delivery_man',
            'contact' => '0779988111',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->deliveryUser = User::find($userId);
    }

    /** @test */
    public function delivery_man_can_access_domex_import_page()
    {
        $this->actingAs($this->deliveryUser);

        $response = $this->get(route('delivery.domex-import'));
        $response->assertStatus(200);
        $response->assertSee('Domex Remittance');
        $response->assertSee('Reconciliation');
    }

    /** @test */
    public function domex_import_parses_excel_and_calculates_variances()
    {
        $this->actingAs($this->deliveryUser);

        // Create a matched test sale with tracking EC707069090
        $customer = Customer::create([
            'name' => 'Mrs K Kobenath',
            'phone' => '0771122334',
            'address' => 'Jaffna',
        ]);

        $sale = Sale::create([
            'sale_id' => 'S-DMX-'.uniqid(),
            'invoice_number' => 'INV-DMX-'.uniqid(),
            'customer_id' => $customer->id,
            'user_id' => $this->deliveryUser->id,
            'delivered_by' => $this->deliveryUser->id,
            'subtotal' => 23400,
            'total_amount' => 23850,
            'due_amount' => 23850,
            'order_source' => 'delivery_man',
            'delivery_status' => 'delivered',
            'payment_status' => 'pending',
            'status' => 'confirm',
        ]);

        DeliverySale::create([
            'sale_id' => $sale->id,
            'delivery_method' => 'Domex',
            'delivery_charge' => 450, // System estimated fee
            'tracking_number' => 'EC707069090',
            'status' => 'In Transit',
        ]);

        $excelPath = base_path('Excel_Export.xlsx');
        $this->assertFileExists($excelPath);

        Livewire::test(DomexRemittanceImport::class)
            ->call('loadFromDiskPath', $excelPath, 'Excel_Export.xlsx')
            ->assertSee('EC707069090')
            ->assertSee('23,850.00') // Domex Value
            ->assertSee('425.00')    // Domex Charge
            ->assertSee('450.00')    // System Charge
            ->assertSee('+Rs. 25.00') // Variance (+25 in store favor)
            ->assertSee('Possible Return') // Zero value rows detected
            ->assertSee('EC707069005');    // Zero value tracking
    }

    /** @test */
    public function delivery_man_can_settle_matched_order_from_domex_import()
    {
        $this->actingAs($this->deliveryUser);

        $customer = Customer::create([
            'name' => 'Settle Customer '.uniqid(),
            'phone' => '0775566778',
            'address' => 'Jaffna Town',
        ]);

        $sale = Sale::create([
            'sale_id' => 'S-SETTLE-'.uniqid(),
            'invoice_number' => 'INV-SETTLE-'.uniqid(),
            'customer_id' => $customer->id,
            'user_id' => $this->deliveryUser->id,
            'delivered_by' => $this->deliveryUser->id,
            'subtotal' => 14000,
            'total_amount' => 14450,
            'due_amount' => 14450,
            'order_source' => 'delivery_man',
            'delivery_status' => 'delivered',
            'payment_status' => 'pending',
            'status' => 'confirm',
        ]);

        $deliverySale = DeliverySale::create([
            'sale_id' => $sale->id,
            'delivery_method' => 'Domex',
            'delivery_charge' => 450,
            'tracking_number' => 'EC707069006',
            'status' => 'In Transit',
        ]);

        $excelPath = base_path('Excel_Export.xlsx');

        Livewire::test(DomexRemittanceImport::class)
            ->call('loadFromDiskPath', $excelPath, 'Excel_Export.xlsx')
            ->call('applySingleSettlement', 'EC707069006')
            ->assertDispatched('show-toast');

        $sale->refresh();
        $deliverySale->refresh();

        $this->assertEquals('completed', $sale->delivery_status);
        $this->assertEquals('paid', $sale->payment_status);
        $this->assertEquals(0, (float) $sale->due_amount);
        $this->assertEquals(400, (float) $deliverySale->delivery_charge); // Domex fee in Excel row 2 is 400
        $this->assertEquals('Completed', $deliverySale->status);

        $payment = Payment::where('sale_id', $sale->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals(14450, (float) $payment->amount);
    }

    /** @test */
    public function delivery_man_can_confirm_return_for_zero_value_order()
    {
        $this->actingAs($this->deliveryUser);

        $customer = Customer::create([
            'name' => 'Return Customer '.uniqid(),
            'phone' => '0773344556',
            'address' => 'Chilaw',
        ]);

        $sale = Sale::create([
            'sale_id' => 'S-RET-'.uniqid(),
            'invoice_number' => 'INV-RET-'.uniqid(),
            'customer_id' => $customer->id,
            'user_id' => $this->deliveryUser->id,
            'delivered_by' => $this->deliveryUser->id,
            'subtotal' => 2000,
            'total_amount' => 2450,
            'due_amount' => 2450,
            'order_source' => 'delivery_man',
            'delivery_status' => 'in_transit',
            'payment_status' => 'pending',
            'status' => 'confirm',
        ]);

        $deliverySale = DeliverySale::create([
            'sale_id' => $sale->id,
            'delivery_method' => 'Domex',
            'delivery_charge' => 450,
            'tracking_number' => 'EC707069005', // Row 1 in Excel: Value = 0, Charge = 400
            'status' => 'In Transit',
        ]);

        $excelPath = base_path('Excel_Export.xlsx');

        Livewire::test(DomexRemittanceImport::class)
            ->call('loadFromDiskPath', $excelPath, 'Excel_Export.xlsx')
            ->call('confirmSingleReturn', 'EC707069005')
            ->assertDispatched('show-toast');

        $sale->refresh();
        $deliverySale->refresh();

        $this->assertEquals('returned', $sale->delivery_status);
        $this->assertEquals('Returned', $deliverySale->status);
        $this->assertEquals(400, (float) $deliverySale->delivery_charge);
        $this->assertNotNull($deliverySale->returned_at);
    }
}
