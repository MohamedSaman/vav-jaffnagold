<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SaleInvoiceNumberUniquenessTest extends TestCase
{
    use DatabaseTransactions;
    public function test_generate_invoice_number_handles_non_numeric_and_prevents_duplicate_001()
    {
        $user = User::factory()->create();
        $customer = Customer::create([
            'name' => 'Test Customer',
            'phone' => '0771234567',
            'type' => 'retail',
        ]);

        // Get current baseline before our test inserts
        $initialNext = (int) Sale::generateInvoiceNumber();

        // Scenario 1: Sale with $initialNext is created
        $inv1 = str_pad($initialNext, 3, '0', STR_PAD_LEFT);
        Sale::create([
            'sale_id' => 'SALE-20261017-TEST01',
            'invoice_number' => $inv1,
            'customer_id' => $customer->id,
            'subtotal' => 1000,
            'discount_amount' => 0,
            'total_amount' => 1000,
            'payment_type' => 'full',
            'payment_status' => 'paid',
            'status' => 'confirm',
            'user_id' => $user->id,
        ]);

        // Next invoice number should be $initialNext + 1
        $next = Sale::generateInvoiceNumber();
        $this->assertEquals(str_pad($initialNext + 1, 3, '0', STR_PAD_LEFT), $next);

        // Scenario 2: Another sale inserted with non-numeric invoice (e.g. POS-SYNC-123 or INV-TEST-ABC)
        Sale::create([
            'sale_id' => 'SALE-20261017-TEST02',
            'invoice_number' => 'POS-SYNC-123-UNIQUE',
            'customer_id' => $customer->id,
            'subtotal' => 1000,
            'discount_amount' => 0,
            'total_amount' => 1000,
            'payment_type' => 'full',
            'payment_status' => 'paid',
            'status' => 'confirm',
            'user_id' => $user->id,
        ]);

        // Old logic would have seen POS-SYNC-123-UNIQUE, parsed intval to 0, and generated '001' (causing 1062 duplicate crash).
        // New logic must continue from the max numeric invoice ($initialNext + 1)
        $nextAfterNonNumeric = Sale::generateInvoiceNumber();
        $this->assertNotEquals('001', $nextAfterNonNumeric);
        $this->assertEquals(str_pad($initialNext + 1, 3, '0', STR_PAD_LEFT), $nextAfterNonNumeric);

        // Create sale with $initialNext + 1
        $inv2 = str_pad($initialNext + 1, 3, '0', STR_PAD_LEFT);
        Sale::create([
            'sale_id' => 'SALE-20261017-TEST03',
            'invoice_number' => $inv2,
            'customer_id' => $customer->id,
            'subtotal' => 1000,
            'discount_amount' => 0,
            'total_amount' => 1000,
            'payment_type' => 'full',
            'payment_status' => 'paid',
            'status' => 'confirm',
            'user_id' => $user->id,
        ]);

        // Scenario 3: Insert another non-numeric row
        Sale::create([
            'sale_id' => 'SALE-20261017-TEST04',
            'invoice_number' => 'INV-ONLINE-ORDER-UNIQUE',
            'customer_id' => $customer->id,
            'subtotal' => 1000,
            'discount_amount' => 0,
            'total_amount' => 1000,
            'payment_type' => 'full',
            'payment_status' => 'paid',
            'status' => 'confirm',
            'user_id' => $user->id,
        ]);

        // Next number must now be $initialNext + 2
        $nextAfterSecond = Sale::generateInvoiceNumber();
        $this->assertEquals(str_pad($initialNext + 2, 3, '0', STR_PAD_LEFT), $nextAfterSecond);
    }

    public function test_generate_sale_id_prevents_duplicate_collisions()
    {
        $user = User::factory()->create();
        $customer = Customer::create([
            'name' => 'Test Customer 2',
            'phone' => '0777654321',
            'type' => 'retail',
        ]);

        $firstId = Sale::generateSaleId();
        Sale::create([
            'sale_id' => $firstId,
            'invoice_number' => 'INV-TEST-COLLISION-'.uniqid(),
            'customer_id' => $customer->id,
            'subtotal' => 1000,
            'discount_amount' => 0,
            'total_amount' => 1000,
            'payment_type' => 'full',
            'payment_status' => 'paid',
            'status' => 'confirm',
            'user_id' => $user->id,
        ]);

        $secondId = Sale::generateSaleId();
        $this->assertNotEquals($firstId, $secondId);
    }
}
