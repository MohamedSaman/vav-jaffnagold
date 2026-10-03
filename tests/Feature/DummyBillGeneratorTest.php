<?php

namespace Tests\Feature;

use App\Livewire\ShopStaff\ShopStaffSalesList;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class DummyBillGeneratorTest extends TestCase
{
    use DatabaseTransactions;

    protected User $staffUser;

    protected Sale $sale;

    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $userId = DB::table('users')->insertGetId([
            'name' => 'Shop Cashier',
            'email' => 'cashier_'.uniqid().'@jaffnagold.lk',
            'password' => bcrypt('password'),
            'role' => 'staff',
            'contact' => '0771234567',
            'staff_type' => 'shop_staff',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->staffUser = User::find($userId);

        $customer = Customer::create([
            'name' => 'Dillaniy',
            'phone' => '0771122334',
            'email' => 'dillaniy_'.uniqid().'@example.com',
            'type' => 'retail',
        ]);

        $invoiceNo = 'INV-TEST-'.uniqid();
        $this->sale = Sale::create([
            'sale_id' => 'SL-'.uniqid(),
            'invoice_number' => $invoiceNo,
            'customer_id' => $customer->id,
            'subtotal' => 323150,
            'discount_amount' => 0,
            'total_amount' => 323150,
            'payment_status' => 'paid',
            'payment_type' => 'full',
            'due_amount' => 0,
            'user_id' => $this->staffUser->id,
            'status' => 'confirm',
        ]);

        SaleItem::create([
            'sale_id' => $this->sale->id,
            'product_name' => '22K Handcrafted Gold Bangle',
            'product_code' => 'BGL-22K-01',
            'quantity' => 1,
            'unit_price' => 173150,
            'total' => 173150,
        ]);

        SaleItem::create([
            'sale_id' => $this->sale->id,
            'product_name' => '22K Traditional Gold Necklace',
            'product_code' => 'NCK-22K-02',
            'quantity' => 1,
            'unit_price' => 150000,
            'total' => 150000,
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->actingAs($this->staffUser);
    }

    public function test_open_dummy_bill_modal_populates_products_without_cancelling_them(): void
    {
        Livewire::test(ShopStaffSalesList::class)
            ->call('openDummyBillModal', $this->sale->id)
            ->assertSet('showDummyModal', true)
            ->assertSet('dummyInvoiceNumber', $this->sale->invoice_number)
            ->assertSet('dummyOriginalTotal', 323150.0)
            ->assertCount('dummyItems', 2);
    }

    public function test_apply_target_total_reduces_prices_to_exact_200000(): void
    {
        Livewire::test(ShopStaffSalesList::class)
            ->call('openDummyBillModal', $this->sale->id)
            ->set('dummyTargetTotal', 200000)
            ->call('applyTargetTotal')
            ->assertSet('calculatedDummyTotal', 200000.0);
    }

    public function test_manual_adjustment_in_edit_view_bill_recalculates_totals(): void
    {
        $test = Livewire::test(ShopStaffSalesList::class)
            ->call('openDummyBillModal', $this->sale->id)
            ->set('dummyTargetTotal', 200000)
            ->call('applyTargetTotal');

        // Manually adjust item 0 price to 110,000 and item 1 price to 90,000
        $test->set('dummyItems.0.unit_price', 110000)
            ->call('recalculateItemTotal', 0)
            ->set('dummyItems.1.unit_price', 90000)
            ->call('recalculateItemTotal', 1)
            ->assertSet('calculatedDummyTotal', 200000.0);
    }

    public function test_confirm_and_print_stores_session_data(): void
    {
        Livewire::test(ShopStaffSalesList::class)
            ->call('openDummyBillModal', $this->sale->id)
            ->set('dummyTargetTotal', 200000)
            ->call('applyTargetTotal')
            ->call('confirmAndPrintDummyThermal');

        $sessionData = session('dummy_sale_print');
        $this->assertNotEmpty($sessionData);
        $this->assertEquals($this->sale->id, $sessionData['sale_id']);
        $this->assertEquals($this->sale->invoice_number, $sessionData['invoice_number']);
        $this->assertEquals(200000.0, $sessionData['total_amount']);
        $this->assertCount(2, $sessionData['items']);
    }

    public function test_print_dummy_sale_thermal_and_a4_endpoints_render_with_200000(): void
    {
        session(['dummy_sale_print' => [
            'sale_id' => $this->sale->id,
            'invoice_number' => $this->sale->invoice_number,
            'total_amount' => 200000.0,
            'items' => [
                [
                    'product_name' => '22K Handcrafted Gold Bangle',
                    'product_code' => 'BGL-22K-01',
                    'quantity' => 1,
                    'unit_price' => 107164.0,
                    'total' => 107164.0,
                ],
                [
                    'product_name' => '22K Traditional Gold Necklace',
                    'product_code' => 'NCK-22K-02',
                    'quantity' => 1,
                    'unit_price' => 92836.0,
                    'total' => 92836.0,
                ],
            ],
        ]]);

        $responseThermal = $this->get('/shop-staff/print/dummy-sale-thermal');
        $responseThermal->assertStatus(200);
        $responseThermal->assertSee($this->sale->invoice_number);
        $responseThermal->assertSee('200,000');
        $responseThermal->assertSee('22K Handcrafted Gold Bangle');

        $responseA4 = $this->get('/shop-staff/print/dummy-sale-a4');
        $responseA4->assertStatus(200);
        $responseA4->assertSee($this->sale->invoice_number);
        $responseA4->assertSee('200,000.00');

        // Verify original sale in database was NEVER touched
        $this->sale->refresh();
        $this->assertEquals(323150.0, (float) $this->sale->total_amount);
    }
}
