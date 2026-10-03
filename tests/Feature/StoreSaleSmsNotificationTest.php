<?php

namespace Tests\Feature;

use App\Http\Controllers\PublicInvoiceController;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class StoreSaleSmsNotificationTest extends TestCase
{
    use DatabaseTransactions;

    protected User $staffUser;

    protected Sale $sale;

    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $userId = DB::table('users')->insertGetId([
            'name' => 'Store Staff Member',
            'email' => 'store_staff_'.uniqid().'@jaffnagold.lk',
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
            'name' => 'Kandeepan S.',
            'phone' => '0779876543',
            'email' => 'kandeepan_'.uniqid().'@example.com',
            'type' => 'retail',
        ]);

        $this->sale = Sale::create([
            'sale_id' => 'SL-TEST-'.uniqid(),
            'invoice_number' => 'INV-TEST-'.uniqid(),
            'customer_id' => $customer->id,
            'walking_customer_name' => null,
            'walking_customer_phone' => null,
            'subtotal' => 25000,
            'discount_amount' => 1000,
            'total_amount' => 24000,
            'payment_status' => 'paid',
            'payment_type' => 'full',
            'due_amount' => 0,
            'user_id' => $this->staffUser->id,
            'status' => 'confirm',
        ]);

        SaleItem::create([
            'sale_id' => $this->sale->id,
            'product_name' => '22K Traditional Gold Ring',
            'product_code' => 'RNG-22K-001',
            'quantity' => 1,
            'unit_price' => 25000,
            'discount_per_unit' => 1000,
            'total_discount' => 1000,
            'total' => 24000,
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->actingAs($this->staffUser);
    }

    public function test_public_invoice_token_generation_and_access(): void
    {
        $token = PublicInvoiceController::generateToken($this->sale);
        $url = PublicInvoiceController::generateUrl($this->sale);

        $this->assertNotEmpty($token);
        $this->assertStringContainsString('/invoice/'.$token, $url);

        // Guest accesses invoice without login
        auth()->logout();

        $response = $this->get('/invoice/'.$token);

        $response->assertStatus(200);
        $response->assertSee('JAFFNA');
        $response->assertSee('GOLD');
        $response->assertSee($this->sale->invoice_number);
        $response->assertSee('24,000.00');
        $response->assertSee('22K Traditional Gold Ring');
        $response->assertSee('Kandeepan S.');
    }

    public function test_public_invoice_returns_404_for_tampered_token(): void
    {
        auth()->logout();

        $response = $this->get('/invoice/completely_invalid_token');
        $response->assertStatus(404);

        $token = PublicInvoiceController::generateToken($this->sale);
        $tamperedToken = $token.'extra';
        $response2 = $this->get('/invoice/'.$tamperedToken);
        $response2->assertStatus(404);
    }

    public function test_sms_service_notify_store_sale_dispatches_with_link(): void
    {
        Http::fake([
            'https://smslenz.lk/*' => Http::response([
                'success' => true,
                'message' => 'SMS queued successfully',
                'data' => [
                    'status' => 'success',
                ],
            ], 200),
        ]);

        $result = SmsService::notifyStoreSale($this->sale);

        $this->assertTrue($result['success']);

        Http::assertSent(function ($request) {
            $data = $request->data();

            return str_contains($data['contact'] ?? '', '94779876543')
                && str_contains($data['message'] ?? '', $this->sale->invoice_number)
                && str_contains($data['message'] ?? '', '24,000.00')
                && str_contains($data['message'] ?? '', '/invoice/');
        });
    }

    public function test_sms_service_works_for_walking_customer(): void
    {
        Http::fake([
            'https://smslenz.lk/*' => Http::response([
                'success' => true,
                'message' => 'SMS queued successfully',
            ], 200),
        ]);

        $walkingSale = Sale::create([
            'sale_id' => 'SL-WALK-'.uniqid(),
            'invoice_number' => 'INV-WALK-'.uniqid(),
            'customer_id' => null,
            'walking_customer_name' => 'Priya Raman',
            'walking_customer_phone' => '0761234567',
            'subtotal' => 15000,
            'discount_amount' => 0,
            'total_amount' => 15000,
            'payment_status' => 'partial',
            'payment_type' => 'partial',
            'due_amount' => 5000,
            'user_id' => $this->staffUser->id,
            'status' => 'confirm',
        ]);

        SaleItem::create([
            'sale_id' => $walkingSale->id,
            'product_name' => 'Silver Anklet Pair',
            'product_code' => 'SLV-ANK-01',
            'quantity' => 1,
            'unit_price' => 15000,
            'total' => 15000,
        ]);

        $result = SmsService::notifyStoreSale($walkingSale);

        $this->assertTrue($result['success']);

        Http::assertSent(function ($request) use ($walkingSale) {
            $data = $request->data();

            return str_contains($data['contact'] ?? '', '94761234567')
                && str_contains($data['message'] ?? '', 'Priya Raman')
                && str_contains($data['message'] ?? '', $walkingSale->invoice_number)
                && str_contains($data['message'] ?? '', 'Due: Rs.5,000.00');
        });
    }

    public function test_sms_service_skips_when_no_phone(): void
    {
        $noPhoneSale = Sale::create([
            'sale_id' => 'SL-NOPHONE-'.uniqid(),
            'invoice_number' => 'INV-NOPHONE-'.uniqid(),
            'customer_id' => null,
            'walking_customer_name' => 'Walking Customer',
            'walking_customer_phone' => null,
            'subtotal' => 5000,
            'discount_amount' => 0,
            'total_amount' => 5000,
            'payment_status' => 'paid',
            'payment_type' => 'full',
            'due_amount' => 0,
            'user_id' => $this->staffUser->id,
            'status' => 'confirm',
        ]);

        $result = SmsService::notifyStoreSale($noPhoneSale);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('No valid phone', $result['message']);
    }

    public function test_store_billing_livewire_can_resend_sms(): void
    {
        Http::fake([
            'https://smslenz.lk/*' => Http::response([
                'success' => true,
                'message' => 'SMS sent',
            ], 200),
        ]);

        Livewire::test(\App\Livewire\ShopStaff\StoreBilling::class)
            ->call('sendSaleSms', $this->sale->id)
            ->assertSuccessful();

        Http::assertSent(function ($request) {
            return str_contains($request->data()['contact'] ?? '', '94779876543');
        });
    }
}
