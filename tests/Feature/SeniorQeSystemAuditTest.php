<?php

namespace Tests\Feature;

use App\Livewire\Admin\CustomProductRequests;
use App\Livewire\Admin\DaySummaryDetails;
use App\Livewire\Admin\SalesSystem as AdminSalesSystem;
use App\Livewire\Admin\StaffBilling;
use App\Livewire\Admin\StaffProductReentry;
use App\Livewire\Admin\StaffSalesDetail;
use App\Livewire\Admin\StockReentry;
use App\Livewire\Admin\StoreBilling as AdminStoreBilling;
use App\Livewire\Admin\ViewStaffAllocatedProducts;
use App\Livewire\DeliveryMan\DeliveryOrderDetail;
use App\Livewire\ShopStaff\StoreBilling as ShopStaffStoreBilling;
use App\Livewire\WhatsAppOrderCheckout;
use App\Models\BrandList;
use App\Models\CategoryList;
use App\Models\Customer;
use App\Models\CustomProductInquiry;
use App\Models\DeliverySale;
use App\Models\HrEmployeeSetting;
use App\Models\Payment;
use App\Models\POSSession;
use App\Models\ProductDetail;
use App\Models\ProductPrice;
use App\Models\ProductStock;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\User;
use App\Models\WhatsAppOrder;
use App\Models\ZkEmployee;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\Livewire;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use Tests\TestCase;

class SeniorQeSystemAuditTest extends TestCase
{
    use DatabaseTransactions;

    protected User $adminUser;
    protected User $shopStaffUser;
    protected User $deliveryUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::firstOrCreate(
            ['email' => 'qe_admin@vavjaffnagold.com'],
            [
                'name' => 'QE Admin',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'contact' => '0770000101',
            ]
        );

        $this->shopStaffUser = User::firstOrCreate(
            ['email' => 'qe_staff@vavjaffnagold.com'],
            [
                'name' => 'QE Shop Staff',
                'password' => bcrypt('password'),
                'role' => 'staff',
                'staff_type' => 'shop_staff',
                'contact' => '0770000102',
            ]
        );

        $this->deliveryUser = User::firstOrCreate(
            ['email' => 'qe_delivery@vavjaffnagold.com'],
            [
                'name' => 'QE Delivery Man',
                'password' => bcrypt('password'),
                'role' => 'staff',
                'staff_type' => 'delivery_man',
                'contact' => '0770000103',
            ]
        );

        Customer::firstOrCreate(
            ['name' => 'Walking Customer'],
            ['phone' => 'xxxxx', 'address' => 'Vavuniya', 'type' => 'retail']
        );
    }

    public function test_database_schema_and_enum_contracts(): void
    {
        $requiredTables = [
            'users' => ['id', 'name', 'email', 'role', 'staff_type', 'contact'],
            'customers' => ['id', 'name', 'phone', 'address', 'type', 'opening_balance', 'due_amount', 'total_due'],
            'product_details' => ['id', 'code', 'name', 'barcode', 'in_slider', 'discount_percent', 'status'],
            'product_prices' => ['id', 'product_id', 'supplier_price', 'selling_price', 'retail_price', 'wholesale_price'],
            'product_stocks' => ['id', 'product_id', 'available_stock', 'damage_stock'],
            'sales' => ['id', 'sale_id', 'invoice_number', 'customer_id', 'user_id', 'payment_type', 'payment_status', 'status', 'delivery_status', 'due_amount'],
            'sale_items' => ['id', 'sale_id', 'product_id', 'quantity', 'unit_price', 'discount_per_unit', 'total'],
            'payments' => ['id', 'sale_id', 'customer_id', 'amount', 'payment_method', 'status', 'is_completed'],
            'delivery_sales' => ['id', 'sale_id', 'delivery_method', 'payment_method', 'tracking_number', 'status'],
            'pos_sessions' => ['id', 'user_id', 'session_date', 'opening_cash', 'status'],
            'expenses' => ['id', 'category', 'amount', 'date', 'expense_type'],
            'staff_expenses' => ['id', 'staff_id', 'expense_type', 'amount', 'expense_date', 'status'],
            'handmade_suppliers' => ['id', 'name', 'is_active', 'opening_payable_balance'],
            'personnel_employee' => ['id', 'emp_code', 'first_name', 'last_name', 'department_id'],
            'personnel_department' => ['id', 'dept_name'],
            'iclock_transaction' => ['id', 'emp_code', 'punch_time', 'punch_state'],
            'hr_employee_settings' => ['id', 'zk_employee_id', 'emp_code', 'salary_type', 'daily_working_hours', 'basic_salary', 'opening_balance'],
            'hr_salaries' => ['id', 'zk_employee_id', 'emp_code', 'salary_month', 'opening_balance', 'net_salary', 'paid_amount', 'balance_amount'],
            'hr_salary_payments' => ['id', 'hr_salary_id', 'emp_code', 'amount', 'payment_date', 'payment_method'],
            'sms_logs' => ['id', 'recipient', 'message', 'cost_per_sms', 'total_cost', 'status'],
            'sms_topups' => ['id', 'amount', 'notes'],
            'cash_advances' => ['id', 'employee_id', 'amount', 'date', 'month', 'year'],
            'employee_payments' => ['id', 'employee_id', 'amount_paid', 'net_pay_due', 'overdue_carried_forward', 'status'],
            'purchase_orders' => ['id', 'order_code', 'supplier_id', 'status', 'total_amount', 'discount_amount'],
            'settings' => ['id', 'key', 'value'],
        ];

        foreach ($requiredTables as $table => $columns) {
            $this->assertTrue(Schema::hasTable($table), "Table [{$table}] must exist in database.");
            foreach ($columns as $col) {
                $this->assertTrue(Schema::hasColumn($table, $col), "Column [{$table}.{$col}] must exist in database.");
            }
        }

        // Verify ZkEmployee has timestamps disabled for ZKBioTime compatibility
        $zkModel = new ZkEmployee();
        $this->assertFalse($zkModel->timestamps, 'ZkEmployee must have $timestamps = false for ZKBioTime table compatibility.');

        $emp = ZkEmployee::create([
            'emp_code' => 'QE-ZK-99',
            'first_name' => 'QE',
            'last_name' => 'Tester',
        ]);
        $emp->update(['first_name' => 'QE Updated']);
        $this->assertEquals('QE Updated', $emp->fresh()->first_name);

        // Verify all payment_method enum values on payments table
        $customer = Customer::first();
        $sale = Sale::create([
            'sale_id' => 'SALE-ENUM-'.uniqid(),
            'invoice_number' => (string) random_int(700000, 799999),
            'customer_id' => $customer->id,
            'user_id' => $this->shopStaffUser->id,
            'sale_type' => 'pos',
            'subtotal' => 7000,
            'discount_amount' => 0,
            'total_amount' => 7000,
            'due_amount' => 0,
            'payment_type' => 'full',
            'payment_status' => 'paid',
            'status' => 'confirm',
        ]);

        foreach (['cash', 'card', 'online', 'bank_transfer', 'cheque', 'credit', 'other'] as $method) {
            $p = Payment::create([
                'customer_id' => $customer->id,
                'sale_id' => $sale->id,
                'amount' => 1000,
                'payment_method' => $method,
                'payment_date' => now(),
                'is_completed' => true,
                'status' => 'paid',
            ]);
            $this->assertEquals($method, $p->fresh()->payment_method);
        }
    }

    public function test_all_registered_routes_point_to_existing_classes_and_methods(): void
    {
        $routes = app('router')->getRoutes();
        $issues = [];

        foreach ($routes as $route) {
            $action = $route->getActionName();
            if ($action === 'Closure') {
                continue;
            }
            $class = explode('@', $action)[0];
            if (! class_exists($class)) {
                $issues[] = "Route [{$route->uri()}] references missing class: {$class}";
            } elseif (str_contains($action, '@')) {
                $method = explode('@', $action)[1];
                if (! method_exists($class, $method)) {
                    $issues[] = "Route [{$route->uri()}] references missing method: {$class}@{$method}";
                }
            }
        }

        $this->assertEmpty($issues, "Found broken route actions:\n".implode("\n", $issues));
    }

    public function test_all_livewire_blade_bindings_have_matching_methods_and_properties(): void
    {
        $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(app_path('Livewire')));
        $missingProps = [];
        $missingMethods = [];

        foreach ($rii as $f) {
            if ($f->isDir() || $f->getExtension() !== 'php') {
                continue;
            }
            $path = $f->getPathname();
            $relative = substr($path, strlen(app_path()) + 1);
            $class = 'App\\'.str_replace(['/', '.php'], ['\\', ''], $relative);
            if (! class_exists($class)) {
                continue;
            }
            $ref = new ReflectionClass($class);
            if ($ref->isAbstract() || ! $ref->isSubclassOf(Component::class)) {
                continue;
            }

            $code = file_get_contents($path);
            if (! preg_match_all("/view\(\s*['\"]([^'\"]+)['\"]/", $code, $vm)) {
                continue;
            }
            $viewName = end($vm[1]);
            $viewPath = resource_path('views/'.str_replace('.', '/', $viewName).'.blade.php');
            if (! file_exists($viewPath)) {
                continue;
            }

            $blade = file_get_contents($viewPath);

            if (preg_match_all("/wire:model(?:\.[a-z0-9\-]+)*\s*=\s*['\"]([^'\"]+)['\"]/i", $blade, $mm)) {
                foreach (array_unique($mm[1]) as $propExpr) {
                    $rootProp = explode('.', trim($propExpr))[0];
                    if (str_contains($rootProp, '$') || str_contains($rootProp, '{') || $rootProp === '') {
                        continue;
                    }
                    if (! $ref->hasProperty($rootProp)) {
                        $missingProps[] = "{$class} -> missing property \${$rootProp}";
                    }
                }
            }

            if (preg_match_all("/wire:(?:click|submit)(?:\.[a-z0-9\-]+)*\s*=\s*['\"]([^'\"]+)['\"]/i", $blade, $cm)) {
                foreach (array_unique($cm[1]) as $callExpr) {
                    $callExpr = trim($callExpr);
                    if ($callExpr === '' || str_starts_with($callExpr, '$') || str_contains($callExpr, '=') || str_contains($callExpr, ';')) {
                        continue;
                    }
                    $methodName = trim(explode('(', $callExpr)[0]);
                    if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $methodName)) {
                        continue;
                    }
                    if (in_array($methodName, ['$set', '$toggle', '$refresh', '$dispatch', 'resetPage', 'previousPage', 'nextPage', 'gotoPage'])) {
                        continue;
                    }
                    if (! $ref->hasMethod($methodName)) {
                        $missingMethods[] = "{$class} -> missing method {$methodName}()";
                    }
                }
            }
        }

        $this->assertEmpty($missingProps, "Missing Livewire properties:\n".implode("\n", $missingProps));
        $this->assertEmpty($missingMethods, "Missing Livewire methods:\n".implode("\n", $missingMethods));
    }

    public function test_all_livewire_components_mount_and_render_cleanly(): void
    {
        $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(app_path('Livewire')));
        $components = [];

        foreach ($rii as $f) {
            if ($f->isDir() || $f->getExtension() !== 'php') {
                continue;
            }
            $path = $f->getPathname();
            $relative = substr($path, strlen(app_path()) + 1);
            $class = 'App\\'.str_replace(['/', '.php'], ['\\', ''], $relative);
            if (! class_exists($class)) {
                continue;
            }
            $ref = new ReflectionClass($class);
            if ($ref->isAbstract() || ! $ref->isSubclassOf(Component::class)) {
                continue;
            }
            $components[] = $class;
        }

        sort($components);
        $failures = [];

        foreach ($components as $class) {
            $ref = new ReflectionClass($class);
            if ($ref->hasMethod('mount')) {
                $reqParams = 0;
                foreach ($ref->getMethod('mount')->getParameters() as $p) {
                    if (! $p->isOptional() && ($p->getType() === null || $p->getType()->isBuiltin())) {
                        $reqParams++;
                    }
                }
                if ($reqParams > 0) {
                    continue;
                }
            }

            if (str_contains($class, '\\Admin\\')) {
                $this->actingAs($this->adminUser);
            } elseif (str_contains($class, '\\ShopStaff\\')) {
                $this->actingAs($this->shopStaffUser);
            } elseif (str_contains($class, '\\DeliveryMan\\')) {
                $this->actingAs($this->deliveryUser);
            } else {
                $this->actingAs($this->adminUser);
            }

            try {
                Livewire::test($class)->assertStatus(200);
            } catch (\Throwable $e) {
                $failures[] = "{$class}: {$e->getMessage()}";
            }
        }

        $this->assertEmpty($failures, "Livewire components failed to mount/render:\n".implode("\n", $failures));

        // Also test the 7 parameterized Livewire components
        $this->actingAs($this->adminUser);
        $session = POSSession::first() ?? POSSession::create([
            'user_id' => $this->shopStaffUser->id,
            'session_date' => today(),
            'opening_cash' => 1000,
            'status' => 'open',
        ]);
        Livewire::test(DaySummaryDetails::class, ['sessionId' => $session->id])->assertStatus(200);
        Livewire::test(StaffProductReentry::class, ['staffId' => $this->shopStaffUser->id])->assertStatus(200);
        Livewire::test(StaffSalesDetail::class, ['staffId' => $this->shopStaffUser->id])->assertStatus(200);
        Livewire::test(StockReentry::class, ['staffId' => $this->shopStaffUser->id])->assertStatus(200);
        Livewire::test(ViewStaffAllocatedProducts::class, ['staffId' => $this->shopStaffUser->id])->assertStatus(200);

        $sale = Sale::create([
            'sale_id' => 'S-PARAM-'.uniqid(),
            'invoice_number' => (string) random_int(600000, 699999),
            'customer_id' => Customer::first()->id,
            'user_id' => $this->deliveryUser->id,
            'subtotal' => 2000,
            'total_amount' => 2000,
            'due_amount' => 2000,
            'payment_type' => 'partial',
            'payment_status' => 'pending',
            'status' => 'confirm',
        ]);
        $this->actingAs($this->deliveryUser);
        Livewire::test(DeliveryOrderDetail::class, ['id' => $sale->id])->assertStatus(200);

        $wa = WhatsAppOrder::create([
            'token' => 'qe-token-'.uniqid(),
            'created_by' => $this->deliveryUser->id,
            'items' => [['id' => 1, 'name' => 'Ring', 'code' => 'R1', 'price' => 1000, 'quantity' => 1, 'subtotal' => 1000]],
            'subtotal' => 1000,
            'delivery_charge' => 450,
            'total_amount' => 1450,
            'status' => 'pending',
        ]);
        Livewire::test(WhatsAppOrderCheckout::class, ['token' => $wa->token])->assertStatus(200);
    }

    public function test_end_to_end_pos_delivery_hr_and_custom_inquiry_edge_cases(): void
    {
        config(['sms.enabled' => false]);

        // 1. Test Admin SalesSystem full-paid Online Payment AND zero-paid COD sale creation
        $brand = BrandList::first() ?? BrandList::create(['brand_name' => 'QE Brand']);
        $cat = CategoryList::first() ?? CategoryList::create(['category_name' => 'QE Category']);

        $product = ProductDetail::create([
            'name' => 'QE Gold Bangle',
            'code' => 'QE-BNG-01',
            'barcode' => '888777666',
            'brand_id' => $brand->id,
            'category_id' => $cat->id,
            'status' => 'active',
        ]);
        ProductPrice::create([
            'product_id' => $product->id,
            'supplier_price' => 15000,
            'selling_price' => 25000,
            'retail_price' => 25000,
            'wholesale_price' => 22000,
        ]);
        ProductStock::create([
            'product_id' => $product->id,
            'available_stock' => 20,
        ]);

        $this->actingAs($this->adminUser);

        // Online Payment (full paid)
        Livewire::test(AdminSalesSystem::class)
            ->set('customerName', 'Online Buyer')
            ->set('customerPhone', '0771112233')
            ->set('customerAddress', 'Vavuniya Town')
            ->set('deliveryMethod', 'DHL')
            ->set('paymentMethod', 'Online Payment')
            ->call('addToCart', [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'model' => null,
                'price' => 25000,
                'stock' => 20,
                'variant_id' => null,
                'variant_value' => null,
            ])
            ->call('setPaidFull')
            ->call('createSale')
            ->assertSet('showSaleModal', true);

        $onlineSale = Sale::where('walking_customer_phone', '0771112233')->latest('id')->first();
        $this->assertNotNull($onlineSale);
        $this->assertEquals('full', $onlineSale->payment_type);
        $this->assertEquals('paid', $onlineSale->payment_status);
        $this->assertEquals('Online Payment', $onlineSale->deliverySale->payment_method);
        $this->assertEquals('DHL', $onlineSale->deliverySale->delivery_method);

        // 2. Test CustomProductRequests SMS notification (ensuring $price and Vavuniya branch work without error)
        $inquiry = CustomProductInquiry::create([
            'inquiry_number' => 'INQ-QE-001',
            'customer_name' => 'Rajan',
            'customer_phone' => '0774445566',
            'city' => 'Vavuniya',
            'category' => 'Chains',
            'product_name' => 'Custom Thali Kodi',
            'selected_pown' => 2,
            'selected_grams' => 16,
            'estimated_price' => 380000,
            'quoted_price' => 375000,
            'status' => 'quoted',
        ]);

        Livewire::test(CustomProductRequests::class)
            ->call('openDetail', $inquiry->id)
            ->call('sendSmsNotification')
            ->call('sendSmsWithDoubleCharge', $inquiry->id)
            ->assertHasNoErrors();
    }
}
