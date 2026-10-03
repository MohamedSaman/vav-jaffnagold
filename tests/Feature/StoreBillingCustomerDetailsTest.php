<?php

namespace Tests\Feature;

use App\Livewire\ShopStaff\StoreBilling;
use App\Models\Customer;
use App\Models\POSSession;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class StoreBillingCustomerDetailsTest extends TestCase
{
    use DatabaseTransactions;

    protected $shopStaff;
    protected $walkingCustomer;

    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $userId = DB::table('users')->insertGetId([
            'name'              => 'Test Shop Staff',
            'email'             => 'testshop_' . uniqid() . '@example.com',
            'password'          => bcrypt('password'),
            'role'              => 'staff',
            'contact'           => '1234567890',
            'staff_type'        => 'shop_staff',
            'email_verified_at' => now(),
            'remember_token'    => null,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
        $this->shopStaff = User::find($userId);

        $this->walkingCustomer = Customer::firstOrCreate(
            ['name' => 'Walking Customer'],
            [
                'phone'   => 'xxxxx',
                'email'   => null,
                'address' => 'xxxxx',
                'type'    => 'retail',
            ]
        );

        POSSession::updateOrCreate(
            [
                'user_id'      => $this->shopStaff->id,
                'session_date' => now()->toDateString(),
            ],
            [
                'opening_cash'   => 1000,
                'status'         => 'open',
                'opening_time'   => now(),
            ]
        );

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function test_typed_customer_name_and_phone_are_saved_on_bill()
    {
        $this->actingAs($this->shopStaff);

        $customItem = [
            'key'               => 'custom_item_1',
            'product_id'        => null,
            'is_custom'         => true,
            'code'              => 'CUSTOM-TEST-001',
            'name'              => 'Custom Gold Ring',
            'model'             => 'N/A',
            'quantity'          => 1,
            'price'             => 2500,
            'unit_discount'     => 250,
            'discount'          => 250,
            'total'             => 2250,
            'has_warranty'      => false,
            'warranty_duration' => '1 Year',
        ];

        Livewire::test(StoreBilling::class)
            ->set('cart', [$customItem])
            ->set('customerSearchName', 'Saman')
            ->set('customerSearchPhone', '0759037101')
            ->call('validateAndCreateSale')
            ->assertSet('walkingCustomerName', 'Saman')
            ->assertSet('walkingCustomerPhone', '0759037101')
            ->assertSet('showPaymentModal', true)
            ->set('paymentMethod', 'cash')
            ->set('amountReceived', 2250)
            ->call('completeSaleWithPayment');

        $sale = Sale::where('walking_customer_name', 'Saman')->latest()->first();

        $this->assertNotNull($sale, 'Sale was not created with walking_customer_name Saman');
        $this->assertEquals('0759037101', $sale->walking_customer_phone);
        $this->assertEquals(2250, (float)$sale->total_amount);
    }
}
