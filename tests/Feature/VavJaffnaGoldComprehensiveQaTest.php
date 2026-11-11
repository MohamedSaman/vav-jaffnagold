<?php

namespace Tests\Feature;

use App\Livewire\Admin\Expenses as AdminExpenses;
use App\Livewire\Admin\SalesSystem as AdminSalesSystem;
use App\Livewire\Admin\StoreBilling as AdminStoreBilling;
use App\Livewire\CustomerMonitorSlider;
use App\Livewire\DeliveryMan\DeliveryManDashboard;
use App\Livewire\DeliveryMan\DeliveryManFinance;
use App\Livewire\ShopStaff\ShopStaffExpenses;
use App\Livewire\ShopStaff\StoreBilling as ShopStaffStoreBilling;
use App\Models\BrandList;
use App\Models\CategoryList;
use App\Models\Customer;
use App\Models\DeliverySale;
use App\Models\HrEmployeeSetting;
use App\Models\HrSalary;
use App\Models\HrSalaryPayment;
use App\Models\Payment;
use App\Models\POSSession;
use App\Models\ProductDetail;
use App\Models\ProductPrice;
use App\Models\ProductStock;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\User;
use App\Models\ZkEmployee;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class VavJaffnaGoldComprehensiveQaTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;

    protected User $shopStaffUser;

    protected User $deliveryUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
            'email' => 'qa_admin_'.uniqid().'@vavjaffnagold.lk',
            'contact' => '0761919650',
        ]);

        $this->shopStaffUser = User::factory()->create([
            'role' => 'staff',
            'staff_type' => 'shop_staff',
            'email' => 'qa_staff_'.uniqid().'@vavjaffnagold.lk',
            'contact' => '0761919651',
        ]);

        $this->deliveryUser = User::factory()->create([
            'role' => 'staff',
            'staff_type' => 'delivery_man',
            'email' => 'qa_delivery_'.uniqid().'@vavjaffnagold.lk',
            'contact' => '0761919652',
        ]);

        Customer::firstOrCreate(
            ['name' => 'Walking Customer'],
            ['phone' => 'xxxxx', 'address' => 'Vavuniya', 'type' => 'retail']
        );

        POSSession::updateOrCreate(
            ['user_id' => $this->shopStaffUser->id, 'session_date' => now()->toDateString()],
            ['opening_cash' => 5000, 'status' => 'open', 'opened_at' => now()]
        );

        POSSession::updateOrCreate(
            ['user_id' => $this->adminUser->id, 'session_date' => now()->toDateString()],
            ['opening_cash' => 10000, 'status' => 'open', 'opened_at' => now()]
        );
    }

    public function test_vavuniya_branch_configuration_and_branding(): void
    {
        $this->assertEquals('Vavuniya Branch', config('shop.branch'));
        $this->assertEquals('VAV', config('shop.branch_code'));
        $this->assertEquals('2nd Cross Street, Vavuniya', config('shop.address'));

        $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'))
            ->assertStatus(200)
            ->assertSee('Vavuniya Branch');

        $this->actingAs($this->shopStaffUser)
            ->get(route('shop-staff.dashboard'))
            ->assertStatus(200)
            ->assertSee('Vavuniya Branch');
    }

    public function test_store_billing_displays_default_discount_badge_and_opening_balance_row(): void
    {
        Setting::updateOrCreate(
            ['key' => 'pos_default_discount_percentage'],
            ['value' => '20', 'date' => now()]
        );

        // ShopStaff StoreBilling
        $this->actingAs($this->shopStaffUser);
        Livewire::test(ShopStaffStoreBilling::class)
            ->assertSee('DEFAULT DISC:')
            ->assertSee('20%')
            ->assertSee('Opening Balance')
            ->call('updateDefaultDiscountPercentage', 18)
            ->assertSet('defaultRetailDiscountPercentage', 18.0);

        $this->assertEquals('18', Setting::where('key', 'pos_default_discount_percentage')->value('value'));

        // Admin StoreBilling
        $this->actingAs($this->adminUser);
        Livewire::test(AdminStoreBilling::class)
            ->assertSee('DEFAULT DISC:')
            ->assertSee('18%')
            ->assertSee('Opening')
            ->call('updateDefaultDiscountPercentage', 20)
            ->assertSet('defaultRetailDiscountPercentage', 20.0);

        // Admin SalesSystem
        Livewire::test(AdminSalesSystem::class)
            ->assertSee('Default Disc:')
            ->assertSee('20%')
            ->call('updateDefaultDiscountPercentage', 15)
            ->assertSet('defaultRetailDiscountPercentage', 15.0);
    }

    public function test_payment_method_enum_supports_card_and_other_and_multi_payment_auto_balance(): void
    {
        $sale = Sale::create([
            'sale_id' => 'SALE-QA-'.uniqid(),
            'invoice_number' => (string) random_int(800000, 899999),
            'customer_id' => Customer::first()->id,
            'user_id' => $this->shopStaffUser->id,
            'sale_type' => 'pos',
            'subtotal' => 10000,
            'discount_amount' => 0,
            'total_amount' => 10000,
            'due_amount' => 0,
            'payment_type' => 'full',
            'payment_status' => 'paid',
            'status' => 'confirm',
        ]);

        // Verify 'card' and 'other' enum values insert cleanly into payments table
        $cardPayment = Payment::create([
            'customer_id' => $sale->customer_id,
            'sale_id' => $sale->id,
            'amount' => 6000,
            'payment_method' => 'card',
            'payment_date' => now(),
            'is_completed' => true,
            'status' => 'paid',
        ]);

        $otherPayment = Payment::create([
            'customer_id' => $sale->customer_id,
            'sale_id' => $sale->id,
            'amount' => 4000,
            'payment_method' => 'other',
            'payment_date' => now(),
            'is_completed' => true,
            'status' => 'paid',
        ]);

        $this->assertEquals('card', $cardPayment->fresh()->payment_method);
        $this->assertEquals('other', $otherPayment->fresh()->payment_method);

        // Verify multi-payment auto-balance calculation in ShopStaff StoreBilling
        $this->actingAs($this->shopStaffUser);
        $component = app(ShopStaffStoreBilling::class);
        $component->mount();
        $component->cart = [[
            'key' => 'k_mp',
            'id' => 1,
            'product_id' => 1,
            'variant_id' => null,
            'variant_value' => null,
            'name' => 'Gold Chain',
            'code' => 'GC-01',
            'model' => null,
            'price' => 10000,
            'quantity' => 1,
            'discount' => 0,
            'discount_type' => 'percentage',
            'discount_percentage' => 0,
            'total' => 10000,
            'stock' => 10,
            'pending' => 0,
            'image' => null,
            'batch_numbers' => null,
            'is_custom' => false,
        ]];
        $component->paymentMode = 'multiple';
        $component->updatedPaymentMode('multiple');
        $component->updatedAmountReceived(4000);

        // Auto-filled remaining balance should be 6000
        $this->assertEquals(4000.0, (float) $component->cashAmount);
        $this->assertEquals(6000.0, (float) $component->bankTransferAmount);
    }

    public function test_customer_monitor_slider_feature_and_catalog_endpoint(): void
    {
        $brandId = BrandList::firstOrCreate(['brand_name' => 'Jaffna Gold QA'])->id;
        $catId = CategoryList::firstOrCreate(['category_name' => 'Necklaces QA'])->id;

        $product = ProductDetail::create([
            'name' => 'Showroom Slider Necklace',
            'code' => 'SLD-101',
            'barcode' => '999888777',
            'brand_id' => $brandId,
            'category_id' => $catId,
            'status' => 'active',
            'in_slider' => true,
            'discount_percent' => 15,
        ]);

        ProductPrice::create([
            'product_id' => $product->id,
            'supplier_price' => 10000,
            'selling_price' => 20000,
            'wholesale_price' => 18000,
            'distributor_price' => 17000,
        ]);

        ProductStock::create([
            'product_id' => $product->id,
            'available_stock' => 5,
        ]);

        // Test Livewire component
        Livewire::test(CustomerMonitorSlider::class)
            ->assertStatus(200)
            ->assertSee('Showroom Slider Necklace');

        // Test public route & catalog check JSON endpoint
        $this->get(route('customer.monitor-slider'))
            ->assertStatus(200)
            ->assertSee('Showroom Slider Necklace');

        $this->get(route('customer.display-check'))
            ->assertStatus(200)
            ->assertJsonStructure(['hash', 'count']);
    }

    public function test_dhl_express_delivery_routes_and_finance_reporting(): void
    {
        $sale = Sale::create([
            'sale_id' => 'SALE-DHL-'.uniqid(),
            'invoice_number' => (string) random_int(900000, 999999),
            'customer_id' => Customer::first()->id,
            'user_id' => $this->deliveryUser->id,
            'sale_type' => 'staff',
            'order_source' => 'whatsapp',
            'subtotal' => 25000,
            'discount_amount' => 0,
            'total_amount' => 25000,
            'due_amount' => 25000,
            'payment_type' => 'partial',
            'payment_status' => 'pending',
            'status' => 'confirm',
            'delivery_status' => 'in_transit',
        ]);

        DeliverySale::create([
            'sale_id' => $sale->id,
            'delivery_method' => 'DHL',
            'tracking_number' => 'DHL-77889900',
            'payment_method' => 'Online Payment',
            'status' => 'In Transit',
            'customer_details' => 'Test Customer, London, UK',
        ]);

        $this->actingAs($this->deliveryUser)
            ->get(route('delivery.orders.dhl'))
            ->assertStatus(200)
            ->assertSee('DHL');

        $this->actingAs($this->deliveryUser);
        Livewire::test(DeliveryManFinance::class)
            ->assertStatus(200)
            ->assertSee('DHL');
    }

    public function test_salary_expense_integration_syncs_hr_salary_payments(): void
    {
        $emp = ZkEmployee::create([
            'emp_code' => 'EMPQA01',
            'first_name' => 'Kamal',
            'last_name' => 'Raja',
        ]);

        HrEmployeeSetting::create([
            'zk_employee_id' => $emp->id,
            'emp_code' => $emp->emp_code,
            'basic_salary' => 45000,
            'work_hours_per_day' => 8,
            'working_days_per_month' => 30,
            'overtime_rate_per_hour' => 0,
            'allowances' => 0,
            'default_deductions' => 0,
            'opening_balance' => 10000,
            'is_active' => true,
        ]);

        // Add salary expense via ShopStaffExpenses
        $this->actingAs($this->shopStaffUser);
        Livewire::test(ShopStaffExpenses::class)
            ->set('is_salary', true)
            ->set('salary_employee_id', 'EMPQA01')
            ->set('amount', 3000)
            ->set('description', 'QA Daily Salary Advance')
            ->call('addExpense')
            ->assertHasNoErrors();

        // Verify HrSalaryPayment was automatically synced
        $hrPayment = HrSalaryPayment::where('emp_code', 'EMPQA01')
            ->where('amount', 3000)
            ->first();
        $this->assertNotNull($hrPayment, 'HrSalaryPayment should be created when salary expense is added');
    }
}
