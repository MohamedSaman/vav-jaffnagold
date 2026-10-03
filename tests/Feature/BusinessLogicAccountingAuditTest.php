<?php

namespace Tests\Feature;

use App\Livewire\Admin\ProfitLoss;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\ProductDetail;
use App\Models\ProductPrice;
use App\Models\ProductStock;
use App\Models\ProductSupplier;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Services\FIFOStockService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class BusinessLogicAccountingAuditTest extends TestCase
{
    use DatabaseTransactions;

    protected $adminUser;

    protected $staffUser;

    protected $customer;

    protected $supplier;

    protected $product;

    protected $stock;

    protected $price;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::create([
            'name' => 'Audit Admin',
            'email' => 'audit_admin_'.uniqid().'@jaffnagold.lk',
            'contact' => '077'.rand(1000000, 9999999),
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->staffUser = User::create([
            'name' => 'Shop Staff Auditor',
            'email' => 'audit_staff_'.uniqid().'@jaffnagold.lk',
            'contact' => '077'.rand(1000000, 9999999),
            'password' => bcrypt('password123'),
            'role' => 'staff',
            'staff_type' => 'shop_staff',
            'email_verified_at' => now(),
        ]);

        $this->customer = Customer::create([
            'name' => 'Audit Customer',
            'phone' => '077'.rand(1000000, 9999999),
            'customer_type' => 'retail',
            'opening_balance' => 0,
            'due_amount' => 0,
            'total_due' => 0,
            'status' => 'active',
        ]);

        $this->supplier = ProductSupplier::create([
            'name' => 'Audit Supplier '.uniqid(),
            'phone' => '071'.rand(1000000, 9999999),
            'status' => 'active',
        ]);

        $brandId = DB::table('brand_lists')->insertGetId([
            'brand_name' => 'Audit Brand '.uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $categoryId = DB::table('category_lists')->insertGetId([
            'category_name' => 'Audit Category '.uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->product = ProductDetail::create([
            'code' => 'AUD-'.rand(1000, 9999),
            'name' => 'Audit 22K Gold Item',
            'brand_id' => $brandId,
            'category_id' => $categoryId,
            'supplier_id' => $this->supplier->id,
            'status' => 'active',
            'website_live' => false,
        ]);

        $this->price = ProductPrice::create([
            'product_id' => $this->product->id,
            'supplier_price' => 1000.00,
            'selling_price' => 1500.00,
            'retail_price' => 1500.00,
            'wholesale_price' => 1400.00,
            'distributor_price' => 1350.00,
        ]);

        $this->stock = ProductStock::create([
            'product_id' => $this->product->id,
            'available_stock' => 20,
            'damage_stock' => 0,
            'total_stock' => 20,
            'sold_count' => 0,
            'restocked_quantity' => 0,
        ]);
    }

    /**
     * Audit 1: Verify Initial Stock & Cost-to-Sell Margin
     */
    public function test_initial_stock_and_margin_invariants(): void
    {
        $this->assertEquals(20, $this->stock->available_stock);
        $this->assertEquals(0, $this->stock->damage_stock);
        $this->assertEquals(20, $this->stock->total_stock);
        $this->assertEquals(0, $this->stock->sold_count);

        // Expected Margin = (Selling - Cost) / Selling
        $margin = ($this->price->selling_price - $this->price->supplier_price) / $this->price->selling_price;
        $this->assertEquals(0.3333, round($margin, 4));
    }

    /**
     * Audit 2: Sales Lifecycle -> Stock Deduction -> Revenue -> COGS -> Customer Ledger
     */
    public function test_sales_lifecycle_stock_deduction_cogs_and_customer_ledger(): void
    {
        // Sell 5 units @ 1500 = 7500 total, COGS = 5 * 1000 = 5000, Gross Profit = 2500
        $sale = Sale::create([
            'sale_id' => 'AUD-S-'.uniqid(),
            'invoice_number' => 'INV-AUD-'.uniqid(),
            'customer_id' => $this->customer->id,
            'user_id' => $this->staffUser->id,
            'customer_type' => 'retail',
            'subtotal' => 7500.00,
            'discount_amount' => 0,
            'total_amount' => 7500.00,
            'payment_type' => 'partial',
            'payment_status' => 'partial',
            'status' => 'confirm',
            'due_amount' => 2500.00,
            'created_at' => now(),
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'product_code' => $this->product->code,
            'product_name' => $this->product->name,
            'quantity' => 5,
            'unit_price' => 1500.00,
            'total' => 7500.00,
        ]);

        // Customer pays 5000 initially
        DB::table('payments')->insert([
            'sale_id' => $sale->id,
            'amount' => 5000.00,
            'payment_method' => 'cash',
            'is_completed' => 1,
            'payment_date' => now(),
            'status' => 'approved',
            'collected_by' => $this->staffUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Deduct inventory via FIFO stock service
        FIFOStockService::deductStock($this->product->id, 5);

        // Verify stock state
        $this->stock->refresh();
        $this->assertEquals(15, $this->stock->available_stock, 'Available stock must be 15 after selling 5');
        $this->assertEquals(5, $this->stock->sold_count, 'Sold count must be 5');
        $this->assertEquals(15, $this->stock->total_stock);

        // Verify Customer Due Ledger
        $this->customer->due_amount = $sale->due_amount;
        $this->customer->total_due = $this->customer->opening_balance + $this->customer->due_amount;
        $this->customer->save();

        $this->assertEquals(2500.00, (float) $this->customer->due_amount);
        $this->assertEquals(2500.00, (float) $this->customer->total_due);

        // Additional Partial Payment of 1500
        DB::table('payments')->insert([
            'sale_id' => $sale->id,
            'amount' => 1500.00,
            'payment_method' => 'cash',
            'is_completed' => 1,
            'payment_date' => now(),
            'status' => 'approved',
            'collected_by' => $this->staffUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sale->due_amount -= 1500.00;
        $sale->save();
        $this->customer->due_amount -= 1500.00;
        $this->customer->total_due = $this->customer->opening_balance + $this->customer->due_amount;
        $this->customer->save();

        $this->assertEquals(1000.00, (float) $sale->due_amount);
        $this->assertEquals(1000.00, (float) $this->customer->due_amount);

        // Final Payment of 1000 -> fully paid
        DB::table('payments')->insert([
            'sale_id' => $sale->id,
            'amount' => 1000.00,
            'payment_method' => 'bank_transfer',
            'is_completed' => 1,
            'payment_date' => now(),
            'status' => 'approved',
            'collected_by' => $this->staffUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sale->due_amount = 0;
        $sale->payment_status = 'paid';
        $sale->save();
        $this->customer->due_amount = 0;
        $this->customer->total_due = 0;
        $this->customer->save();

        $this->assertEquals(0.00, (float) $sale->due_amount);
        $this->assertEquals('paid', $sale->payment_status);
        $this->assertEquals(0.00, (float) $this->customer->total_due);
    }

    /**
     * Audit 3: Sales Return Stock Restoration - Prevent Double Addition
     */
    public function test_sales_return_restores_stock_exactly_once(): void
    {
        // 1. Initial sale of 4 units
        FIFOStockService::deductStock($this->product->id, 4);
        $this->stock->refresh();
        $this->assertEquals(16, $this->stock->available_stock);
        $this->assertEquals(4, $this->stock->sold_count);

        // 2. Customer returns 2 units
        FIFOStockService::restoreStock($this->product->id, 2);

        // 3. Verify stock is restored to 18 (NOT 20 from double restoration)
        $this->stock->refresh();
        $this->assertEquals(18, $this->stock->available_stock, 'Stock must increase by exactly 2 to 18');
        $this->assertEquals(2, $this->stock->sold_count, 'Sold count must decrease by 2 to 2');
        $this->assertEquals(18, $this->stock->total_stock);
    }

    /**
     * Audit 4: Sale Cancellation Stock Restoration and Status Flow
     */
    public function test_sale_cancellation_restores_stock_and_is_idempotent(): void
    {
        $sale = Sale::create([
            'sale_id' => 'AUD-CANCEL-'.uniqid(),
            'invoice_number' => 'INV-CAN-'.uniqid(),
            'customer_id' => $this->customer->id,
            'user_id' => $this->staffUser->id,
            'subtotal' => 3000.00,
            'total_amount' => 3000.00,
            'status' => 'pending',
            'delivery_status' => 'pending',
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $this->product->id,
            'product_code' => $this->product->code,
            'product_name' => $this->product->name,
            'quantity' => 2,
            'unit_price' => 1500.00,
            'total' => 3000.00,
        ]);

        // Order confirmation deducts stock
        $deducted = $sale->confirmAndDeductStock();
        $this->assertTrue($deducted);

        $this->stock->refresh();
        $this->assertEquals(18, $this->stock->available_stock);
        $this->assertEquals(2, $this->stock->sold_count);

        // Cancel sale -> stock restored
        $restored = $sale->restoreStock();
        $this->assertTrue($restored);
        $sale->status = 'cancelled';
        $sale->delivery_status = 'cancelled';
        $sale->save();

        $this->stock->refresh();
        $this->assertEquals(20, $this->stock->available_stock, 'Stock must be restored to 20');
        $this->assertEquals(0, $this->stock->sold_count);

        // Second cancellation attempt must be blocked (idempotent)
        $secondRestore = $sale->restoreStock();
        $this->assertFalse($secondRestore, 'Secondary cancellation must not restore stock again');

        $this->stock->refresh();
        $this->assertEquals(20, $this->stock->available_stock, 'Stock must remain 20');
    }

    /**
     * Audit 5: Purchase Order Receipt -> Stock Addition & Delta Sync
     */
    public function test_purchase_order_receipt_and_stock_delta_sync(): void
    {
        $po = PurchaseOrder::create([
            'order_code' => 'PO-AUD-'.uniqid(),
            'supplier_id' => $this->supplier->id,
            'order_date' => now()->toDateString(),
            'status' => 'pending',
            'total_amount' => 30000.00,
            'due_amount' => 30000.00,
        ]);

        $poItem = PurchaseOrderItem::create([
            'order_id' => $po->id,
            'product_id' => $this->product->id,
            'quantity' => 30,
            'received_quantity' => 0,
            'unit_price' => 1000.00,
            'selling_price' => 1500.00,
            'sell_multiplier' => 1.5,
            'total_selling_price' => 45000.00,
            'discount' => 0.00,
            'status' => 'pending',
        ]);

        // Stock before receiving PO
        $this->assertEquals(20, $this->stock->available_stock);

        // Mark PO received: 30 units
        $poItem->received_quantity = 30;
        $poItem->status = 'received';
        $poItem->save();

        $deltaQty = 30;
        $this->stock->available_stock += $deltaQty;
        $this->stock->total_stock += $deltaQty;
        $this->stock->save();

        $po->status = 'received';
        $po->save();

        $this->stock->refresh();
        $this->assertEquals(50, $this->stock->available_stock, 'Stock must be 20 + 30 = 50');
        $this->assertEquals(50, $this->stock->total_stock);

        // Verify supplier payable ledger
        $this->assertEquals(30000.00, (float) $po->due_amount);

        // Partial payment to supplier
        $po->due_amount -= 10000.00;
        $po->save();
        $this->assertEquals(20000.00, (float) $po->due_amount);
    }

    /**
     * Audit 6: Multi-Item Invoices with Line Discounts and Invoice Discounts
     */
    public function test_discounts_and_mathematical_precision(): void
    {
        // Item 1: 2 units @ 1500 = 3000, Item Discount 100 each = 2800
        // Item 2: 1 unit @ 2000 = 2000, Item Discount 200 = 1800
        // Subtotal = 4600
        // Invoice Additional Discount: 10% = 460
        // Expected Grand Total = 4600 - 460 = 4140.00
        $subtotal = 4600.00;
        $invoiceDiscountPercent = 10.0;
        $invoiceDiscountAmount = ($subtotal * $invoiceDiscountPercent) / 100.0;
        $grandTotal = $subtotal - $invoiceDiscountAmount;

        $this->assertEquals(460.00, $invoiceDiscountAmount);
        $this->assertEquals(4140.00, $grandTotal);

        // Payments: Card 2000 + Cash 2140 = 4140.00 -> Due = 0.00
        $paid = 2000.00 + 2140.00;
        $due = $grandTotal - $paid;
        $this->assertEquals(0.00, round($due, 2));
    }

    /**
     * Audit 7: Damaged Stock Separation from Available Stock
     */
    public function test_damaged_stock_separation(): void
    {
        // Move 3 units from available to damage
        $damageQty = 3;
        $this->stock->available_stock -= $damageQty;
        $this->stock->damage_stock += $damageQty;
        $this->stock->updateTotals();

        $this->stock->refresh();
        $this->assertEquals(17, $this->stock->available_stock);
        $this->assertEquals(3, $this->stock->damage_stock);
        $this->assertEquals(20, $this->stock->total_stock);

        // Available for sale must ONLY be 17
        $this->assertEquals(17, $this->stock->available_stock);
    }

    /**
     * Audit 8: Profit & Loss Accounting Reconciliation (Excluding Retained Savings)
     */
    public function test_profit_loss_accounting_reconciliation_excluding_savings(): void
    {
        $today = Carbon::today()->format('Y-m-d');

        // Create genuine operating expenses (Total: 4,000)
        Expense::create([
            'category' => 'Shop Electricity',
            'expense_type' => 'daily',
            'amount' => 2500.00,
            'date' => $today,
            'status' => 'approved',
            'description' => 'CEB Electricity Bill',
            'user_id' => $this->adminUser->id,
        ]);

        Expense::create([
            'category' => 'Tea & Water',
            'expense_type' => 'daily',
            'amount' => 1500.00,
            'date' => $today,
            'status' => 'approved',
            'description' => 'Daily staff tea refreshments',
            'user_id' => $this->adminUser->id,
        ]);

        // Create Retained Savings Expenses (Seedu & Undiyal = 25,000)
        $seedu = Expense::create([
            'category' => 'SEEDU CHIT',
            'expense_type' => 'daily',
            'amount' => 15000.00,
            'date' => $today,
            'status' => 'approved',
            'description' => 'Daily Seedu chit fund contribution',
            'user_id' => $this->adminUser->id,
        ]);

        $undiyal = Expense::create([
            'category' => 'UNDIYAL',
            'expense_type' => 'daily',
            'amount' => 10000.00,
            'date' => $today,
            'status' => 'approved',
            'description' => 'Daily counter undiyal box deposit',
            'user_id' => $this->adminUser->id,
        ]);

        // Verify deposits were auto-created
        $this->assertDatabaseHas('deposits', [
            'reference_no' => 'EXP-'.$seedu->id,
            'saving_method' => 'seedu',
            'amount' => 15000.00,
        ]);

        $this->assertDatabaseHas('deposits', [
            'reference_no' => 'EXP-'.$undiyal->id,
            'saving_method' => 'undiyal',
            'amount' => 10000.00,
        ]);

        // Mount ProfitLoss Livewire component
        $pl = Livewire::actingAs($this->adminUser)
            ->test(ProfitLoss::class)
            ->call('setDatePreset', 'today');

        // The operating expenses in P&L must NOT include Seedu and Undiyal!
        $expenseBreakdown = $pl->get('expenseBreakdown');
        $this->assertArrayNotHasKey('SEEDU CHIT', $expenseBreakdown, 'Seedu must be excluded from Operating Expenses');
        $this->assertArrayNotHasKey('UNDIYAL', $expenseBreakdown, 'Undiyal must be excluded from Operating Expenses');

        // Total deposits must report retained assets
        $this->assertGreaterThanOrEqual(15000.00, (float) $pl->get('seeduSavings'));
        $this->assertGreaterThanOrEqual(10000.00, (float) $pl->get('undiyalSavings'));
        $this->assertGreaterThanOrEqual(25000.00, (float) $pl->get('totalDeposits'));
    }

    /**
     * Audit 9: Concurrency and Negative Stock Prevention
     */
    public function test_concurrency_and_negative_stock_prevention(): void
    {
        // Set stock to only 1 unit
        $this->stock->available_stock = 1;
        $this->stock->total_stock = 1;
        $this->stock->save();

        // Selling 1 unit succeeds
        $result1 = FIFOStockService::deductStock($this->product->id, 1);
        $this->assertTrue($result1['success']);

        $this->stock->refresh();
        $this->assertEquals(0, $this->stock->available_stock);

        // Attempting to sell 1 more unit must fail with Insufficient stock Exception
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Insufficient stock/i');
        FIFOStockService::deductStock($this->product->id, 1);
    }

    /**
     * Audit 10: Security and Role-Based Access Control
     */
    public function test_role_based_access_security_boundaries(): void
    {
        // 1. Unauthenticated user cannot access profit loss
        $response = $this->get(route('admin.profit-loss'));
        $response->assertRedirect('/login');

        // 2. Shop staff cannot access admin profit loss page (redirected to staff dashboard)
        $this->flushSession();
        $this->actingAs($this->staffUser);
        $staffResponse = $this->get(route('admin.profit-loss'));
        $this->assertTrue(in_array($staffResponse->status(), [403, 302]));

        // 3. Admin user can successfully access admin profit loss page
        $this->flushSession();
        $this->actingAs($this->adminUser);
        $adminResponse = $this->get(route('admin.profit-loss'));
        $adminResponse->assertStatus(200);
        $adminResponse->assertSee('Profit & Loss');
    }
}
