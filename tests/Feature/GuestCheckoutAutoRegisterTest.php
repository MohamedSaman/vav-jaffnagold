<?php

namespace Tests\Feature;

use App\Livewire\CustomLogin;
use App\Livewire\Ecommerce\Checkout;
use App\Models\BrandList;
use App\Models\CategoryList;
use App\Models\Customer;
use App\Models\EcommerceOrder;
use App\Models\ProductDetail;
use App\Models\ProductPrice;
use App\Models\ProductStock;
use App\Models\ProductSupplier;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GuestCheckoutAutoRegisterTest extends TestCase
{
    use DatabaseTransactions;

    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $brand = BrandList::firstOrCreate(['brand_name' => 'Jaffna Gold Brand']);
        $cat = CategoryList::firstOrCreate(['category_name' => 'Gold Rings Test']);
        $supplier = ProductSupplier::firstOrCreate(
            ['name' => 'Test Supplier'],
            ['contact' => '0770000000', 'email' => 'sup@test.com', 'address' => 'Colombo']
        );

        $this->product = ProductDetail::create([
            'name' => '22K Gold Solitaire Ring',
            'code' => 'RNG-' . uniqid(),
            'barcode' => 'BC' . rand(100000, 999999),
            'brand_id' => $brand->id,
            'category_id' => $cat->id,
            'supplier_id' => $supplier->id,
            'slug' => '22k-gold-solitaire-ring-' . uniqid(),
            'status' => 'active',
        ]);

        ProductPrice::create([
            'product_id' => $this->product->id,
            'supplier_price' => 50000,
            'retail_price' => 60000,
            'selling_price' => 60000,
        ]);

        ProductStock::create([
            'product_id' => $this->product->id,
            'total_stock' => 10,
            'available_stock' => 10,
            'damage_stock' => 0,
            'sold_count' => 0,
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        Auth::logout();
        session()->flush();
    }

    /** @test */
    public function guest_checkout_places_order_and_auto_registers_customer_with_default_password()
    {
        // Setup session cart
        session()->put('cart', [
            $this->product->id => 2,
        ]);

        $guestPhone = '0779876543';
        $guestEmail = 'guest_' . uniqid() . '@example.com';

        $checkout = new Checkout();
        $checkout->mount();

        $this->assertCount(1, $checkout->cartItems);
        $this->assertEquals(2, $checkout->cartItems[0]['quantity']);

        // Fill in checkout details as a guest
        $checkout->first_name = 'Saman';
        $checkout->last_name = 'Kumara';
        $checkout->phone = $guestPhone;
        $checkout->email = $guestEmail;
        $checkout->address1 = '45 Main Street';
        $checkout->city = 'Jaffna';
        $checkout->country = 'Sri Lanka';
        $checkout->payment_method = 'COD';

        $response = $checkout->placeOrder();

        // 1. Check that User was auto-created
        $user = User::where('contact', $guestPhone)->first();
        $this->assertNotNull($user, 'User should be auto-created in the background');
        $this->assertEquals('Saman Kumara', $user->name);
        $this->assertEquals('customer', $user->role);
        $this->assertEquals($guestEmail, $user->email);
        $this->assertTrue(Hash::check('password123', $user->password), 'Password should match default password');

        // 2. Check that User is auto-logged in
        $this->assertTrue(Auth::check(), 'Customer should be logged in after checkout');
        $this->assertEquals($user->id, Auth::id());

        // 3. Check Order was created in ecommerce_orders
        $order = EcommerceOrder::where('customer_phone', $guestPhone)->latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals('Saman Kumara', $order->customer_name);
        $this->assertEquals('45 Main Street', $order->shipping_address);
        $this->assertEquals('Jaffna', $order->city);
        $this->assertCount(1, $order->items);
        $this->assertEquals(2, $order->items->first()->quantity);

        // 4. Check Stock was NOT deducted at checkout (remains pending until confirmed)
        $stock = ProductStock::where('product_id', $this->product->id)->first();
        $this->assertEquals(10, $stock->available_stock, 'Stock should not be deducted at checkout');
        $this->assertEquals(0, $stock->sold_count);

        // Confirm order via Sale workflow to deduct stock
        $sale = \App\Models\Sale::where('ecommerce_order_id', $order->id)->first();
        $this->assertNotNull($sale);
        $this->assertEquals('pending', $sale->status);
        $sale->confirmAndDeductStock();

        $stock->refresh();
        $this->assertEquals(8, $stock->available_stock, 'Stock should be deducted only after confirmation');
        $this->assertEquals(2, $stock->sold_count);

        // 5. Check cart was emptied
        $this->assertEmpty(session()->get('cart', []));
    }

    /** @test */
    public function auto_registered_customer_can_login_using_phone_number_and_default_password()
    {
        $phone = '0771122334';
        $defaultPassword = 'password123';

        // Create an auto-registered customer
        $user = User::create([
            'name' => 'Naveen Kumar',
            'email' => 'naveen_' . uniqid() . '@customer.jaffnagold.com',
            'contact' => $phone,
            'role' => 'customer',
            'password' => Hash::make($defaultPassword),
        ]);

        Auth::logout();
        $this->assertFalse(Auth::check());

        // Test CustomLogin with phone number
        $loginComponent = new CustomLogin();
        $loginComponent->email = $phone;
        $loginComponent->password = $defaultPassword;

        $loginComponent->login();

        $this->assertTrue(Auth::check(), 'User should be logged in via phone number');
        $this->assertEquals($user->id, Auth::id());
    }

    /** @test */
    public function logged_in_customer_checkout_links_to_existing_account_without_duplication()
    {
        $existingUser = User::create([
            'name' => 'Existing Customer',
            'email' => 'existing_' . uniqid() . '@example.com',
            'contact' => '0775551234',
            'role' => 'customer',
            'password' => Hash::make('secret123'),
        ]);

        Auth::login($existingUser);

        session()->put('cart', [
            $this->product->id => 1,
        ]);

        $checkout = new Checkout();
        $checkout->mount();

        // Form should prefill
        $this->assertEquals('Existing', $checkout->first_name);
        $this->assertEquals('Customer', $checkout->last_name);
        $this->assertEquals('0775551234', $checkout->phone);
        $this->assertEquals($existingUser->email, $checkout->email);

        $checkout->address1 = '12 Temple Road';
        $checkout->city = 'Nallur';
        $checkout->country = 'Sri Lanka';
        $checkout->payment_method = 'Bank Transfer';

        $checkout->placeOrder();

        // Verify no duplicate users were created
        $usersCount = User::where('contact', '0775551234')->count();
        $this->assertEquals(1, $usersCount);

        // Verify order created
        $order = EcommerceOrder::where('customer_phone', '0775551234')->latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals('Existing Customer', $order->customer_name);
    }
}
