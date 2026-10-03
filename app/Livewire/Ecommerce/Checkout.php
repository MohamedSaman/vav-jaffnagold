<?php

namespace App\Livewire\Ecommerce;

use App\Livewire\Ecommerce\Concerns\InteractsWithStorefront;
use App\Models\Customer;
use App\Models\DeliverySale;
use App\Models\EcommerceOrder;
use App\Models\EcommerceOrderItem;
use App\Models\ProductDetail;
use App\Models\ProductStock;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Checkout')]
#[Layout('components.layouts.webapp')]
class Checkout extends Component
{
    use InteractsWithStorefront;

    public $cartItems = [];
    public $subtotal = 0;
    public $shipping = 0;
    public $total = 0;

    // Streamlined Form Fields
    public $full_name = '';
    public $phone = '';
    public $address = '';
    public $city = '';

    // City Autocomplete state
    public $citySuggestions = [];
    public $showCitySuggestions = false;

    // Legacy fields supported for backward compatibility
    public $first_name = '';
    public $last_name = '';
    public $email = '';
    public $address1 = '';
    public $address2 = '';
    public $state = '';
    public $zip = '';
    public $country = 'Sri Lanka';

    // Payment & Notes
    public $payment_method = 'COD'; // 'COD', 'Bank Transfer', 'Card'
    public $card_number = '';
    public $card_expiry = '';
    public $card_cvv = '';
    public $card_name = '';
    public $notes = '';

    public static $sriLankaCities = [
        'Jaffna', 'Nallur', 'Chavakachcheri', 'Point Pedro', 'Chunnakam',
        'Karainagar', 'Velanai', 'Kayts', 'Valvettithurai', 'Kopay',
        'Tellippalai', 'Manipay', 'Vaddukoddai', 'Atchuvely', 'Kodikamam',
        'Sandilipay', 'Pandateruppu', 'Uduvil', 'Maruthankerny', 'Ilavalai',
        'Kilinochchi', 'Paranthan', 'Poonakary', 'Pallai',
        'Vavuniya', 'Cheddikulam', 'Nedunkeni',
        'Mannar', 'Murunkan', 'Nanaddan', 'Pesalai',
        'Mullaitivu', 'Puthukkudiyiruppu', 'Oddusuddan',
        'Colombo', 'Dehiwala', 'Mount Lavinia', 'Moratuwa', 'Kotte',
        'Battaramulla', 'Maharagama', 'Kotikawatta', 'Kolonnawa', 'Kesbewa',
        'Homagama', 'Kaduwela', 'Piliyandala', 'Nugegoda',
        'Gampaha', 'Negombo', 'Katunayake', 'Kelaniya', 'Ja-Ela', 'Wattala',
        'Kadawatha', 'Minuwangoda', 'Mirigama', 'Kiribathgoda',
        'Kalutara', 'Panadura', 'Horana', 'Beruwala', 'Matugama', 'Aluthgama',
        'Kandy', 'Peradeniya', 'Gampola', 'Nawalapitiya', 'Katugastota',
        'Matale', 'Dambulla', 'Sigiriya', 'Galewela',
        'Nuwara Eliya', 'Hatton', 'Talawakele',
        'Galle', 'Hikkaduwa', 'Ambalangoda', 'Baddegama',
        'Matara', 'Weligama', 'Akuressa', 'Dickwella',
        'Hambantota', 'Tangalle', 'Tissamaharama', 'Beliatta',
        'Batticaloa', 'Kattankudy', 'Eravur', 'Valaichchenai',
        'Trincomalee', 'Kinniya', 'Muttur', 'Kantale',
        'Ampara', 'Kalmunai', 'Sammanthurai', 'Akkaraipattu', 'Potuvil',
        'Kurunegala', 'Kuliyapitiya', 'Narammala', 'Pannala',
        'Puttalam', 'Chilaw', 'Marawila', 'Wennappuwa', 'Nattandiya',
        'Anuradhapura', 'Medawachchiya', 'Kekirawa', 'Eppawala',
        'Polonnaruwa', 'Kaduruwela', 'Hingurakgoda', 'Medirigiriya',
        'Badulla', 'Bandarawela', 'Haputale', 'Ella', 'Mahiyanganaya',
        'Monaragala', 'Wellawaya', 'Bibile', 'Buttala',
        'Ratnapura', 'Balangoda', 'Pelmadulla', 'Embilipitiya', 'Kuruwita',
        'Kegalle', 'Mawanella', 'Warakapola', 'Ruwanwella'
    ];

    public function updatedCity($value = null)
    {
        $term = trim($value ?? $this->city);
        if (strlen($term) >= 1) {
            $matches = array_filter(self::$sriLankaCities, function ($c) use ($term) {
                return stripos($c, $term) !== false;
            });
            $this->citySuggestions = array_values(array_slice($matches, 0, 8));
            $this->showCitySuggestions = count($this->citySuggestions) > 0;
        } else {
            $this->citySuggestions = [];
            $this->showCitySuggestions = false;
        }
    }

    public function selectCity($cityName)
    {
        $this->city = $cityName;
        $this->citySuggestions = [];
        $this->showCitySuggestions = false;
    }

    public function rules()
    {
        return [
            'full_name' => 'required_without:first_name|nullable|string|max:150',
            'first_name' => 'required_without:full_name|nullable|string|max:100',
            'phone' => 'required|string|max:25',
            'address' => 'required_without:address1|nullable|string|max:255',
            'address1' => 'required_without:address|nullable|string|max:255',
            'city' => 'required|string|max:100',
            'payment_method' => 'required|in:COD,Bank Transfer,Card',
            'email' => 'nullable|email|max:255',
        ];
    }

    public function messages()
    {
        return [
            'full_name.required_without' => 'Please enter your full name.',
            'first_name.required_without' => 'Please enter your name.',
            'phone.required' => 'Please enter your phone number.',
            'address.required_without' => 'Please enter your delivery address.',
            'address1.required_without' => 'Please enter your delivery address.',
            'city.required' => 'Please enter your city or town.',
            'payment_method.required' => 'Please select a payment method.',
        ];
    }

    public function mount()
    {
        $this->loadCart();
        if (empty($this->cartItems)) {
            return redirect()->route('cart');
        }

        // Auto-prefill details if customer is already logged in
        if (Auth::check()) {
            $user = Auth::user();
            $this->full_name = $user->name;
            $parts = explode(' ', $user->name, 2);
            $this->first_name = $parts[0] ?? '';
            $this->last_name = $parts[1] ?? '';
            $this->phone = $user->contact ?? '';
            $this->email = $user->email ?? '';

            // Check if customer profile has saved address
            $customer = Customer::where('user_id', $user->id)
                ->orWhere('phone', $user->contact)
                ->orWhere('email', $user->email)
                ->first();

            if ($customer && !empty($customer->address)) {
                $this->address = $customer->address;
                $this->address1 = $customer->address;
            }
        }
    }

    public function loadCart()
    {
        $cart = session()->get('cart', []);
        $this->cartItems = [];
        $this->subtotal = 0;

        if (!empty($cart)) {
            $products = ProductDetail::with('price')
                ->whereIn('id', array_keys($cart))
                ->get();

            foreach ($products as $product) {
                $qty = $cart[$product->id] ?? 1;
                $price = (float) ($product->price?->retail_price ?? $product->price?->selling_price ?? 0) * 0.90;
                $itemSubtotal = $price * $qty;

                $this->cartItems[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code,
                    'price' => $price,
                    'quantity' => $qty,
                    'subtotal' => $itemSubtotal,
                    'line_total' => $itemSubtotal,
                ];

                $this->subtotal += $itemSubtotal;
            }
        }

        $this->total = $this->subtotal + $this->shipping;
    }

    public function placeOrder()
    {
        $this->validate();

        $cart = session()->get('cart', []);
        if (empty($cart)) {
            $this->dispatch('showToast', message: 'Your cart is empty.');
            return;
        }

        try {
            DB::beginTransaction();

            $fullName = trim($this->full_name ?: ($this->first_name . ' ' . $this->last_name));
            $cleanPhone = preg_replace('/[^0-9+]/', '', $this->phone);
            $fullAddress = trim($this->address ?: ($this->address1 . ($this->address2 ? ', ' . $this->address2 : '')));
            $defaultPassword = 'password123'; // Default login password for auto-registered accounts
            $accountCreated = false;

            // ─── 1. User Authentication & Background Auto-Registration ─────────
            if (Auth::check()) {
                $user = Auth::user();
            } else {
                // Check if user already exists by phone (contact) or email
                $user = User::where(function ($q) use ($cleanPhone) {
                    $q->where('contact', $cleanPhone)
                      ->orWhere('contact', ltrim($cleanPhone, '+'))
                      ->orWhere('contact', '0' . substr($cleanPhone, -9));
                });

                if (!empty($this->email)) {
                    $user = $user->orWhere('email', $this->email);
                }

                $user = $user->first();

                if (!$user) {
                    // Generate unique email if none provided
                    $emailToUse = !empty($this->email) ? $this->email : ($cleanPhone . '@customer.jaffnagold.com');
                    if (User::where('email', $emailToUse)->exists()) {
                        $emailToUse = $cleanPhone . '_' . time() . '@customer.jaffnagold.com';
                    }

                    $user = User::create([
                        'name' => $fullName ?: 'Valued Customer',
                        'email' => $emailToUse,
                        'contact' => $cleanPhone,
                        'role' => 'customer',
                        'password' => Hash::make($defaultPassword),
                    ]);

                    $accountCreated = true;
                }

                // Log the customer in automatically
                Auth::login($user, true);
            }

            // Sync Customer Profile record
            $customer = Customer::where('user_id', $user->id)
                ->orWhere('phone', $cleanPhone)
                ->first();

            if (!$customer) {
                $customer = Customer::create([
                    'name' => $fullName ?: $user->name,
                    'phone' => $cleanPhone,
                    'email' => $this->email ?: $user->email,
                    'type' => 'retail',
                    'address' => $fullAddress . ', ' . $this->city,
                    'user_id' => $user->id,
                ]);
            } else if (empty($customer->user_id)) {
                $customer->user_id = $user->id;
                $customer->save();
            }

            // ─── 2. Create E-Commerce Order ────────────────────────────────────
            $orderNumber = EcommerceOrder::generateOrderNumber();

            $order = EcommerceOrder::create([
                'order_number' => $orderNumber,
                'customer_name' => $fullName ?: $user->name,
                'customer_email' => $this->email ?: $user->email,
                'customer_phone' => $cleanPhone,
                'shipping_address' => $fullAddress,
                'city' => $this->city,
                'postal_code' => $this->zip,
                'subtotal' => $this->subtotal,
                'shipping_charge' => $this->shipping,
                'total_amount' => $this->total,
                'payment_method' => $this->payment_method,
                'payment_status' => 'pending',
                'order_status' => 'pending',
                'notes' => $this->notes,
            ]);

            // ─── 3. Create Order Items (Stock deducted strictly upon confirmation) ─
            foreach ($this->cartItems as $item) {
                $productId = $item['id'];
                $qty = $item['quantity'];
                $unitPrice = $item['price'];

                EcommerceOrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'quantity' => $qty,
                    'price' => $unitPrice,
                    'subtotal' => $item['subtotal'],
                ]);
            }

            // ─── 4. Create Sale & DeliverySale for Delivery Hub ─────────────────
            $isCod = (strtoupper($this->payment_method) === 'COD');
            $dueAmount = $isCod ? $this->total : 0;
            $paymentStatus = $isCod ? 'pending' : 'paid';

            $deliveryMan = User::where('role', 'delivery_man')->first()
                ?? User::where('role', 'admin')->first()
                ?? $user;
            $deliveryManId = $deliveryMan->id;

            $sale = Sale::create([
                'sale_id' => Sale::generateSaleId(),
                'invoice_number' => Sale::generateInvoiceNumber(),
                'customer_id' => $customer->id,
                'walking_customer_name' => $fullName ?: $user->name,
                'walking_customer_phone' => $cleanPhone,
                'customer_type' => 'retail',
                'subtotal' => $this->subtotal,
                'discount_amount' => 0,
                'total_amount' => $this->total,
                'due_amount' => $dueAmount,
                'payment_type' => $dueAmount > 0 ? 'partial' : 'full',
                'payment_status' => $paymentStatus,
                'status' => 'pending', // Pending confirmation, stock will be deducted when confirmed
                'sale_type' => 'staff',
                'order_source' => 'ecommerce',
                'ecommerce_order_id' => $order->id,
                'user_id' => $deliveryManId,
                'delivery_status' => 'pending', // Pending Pack
                'notes' => 'Website Store Order (' . $orderNumber . ')' . ($this->notes ? ' | ' . $this->notes : ''),
            ]);

            foreach ($this->cartItems as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['id'],
                    'product_name' => $item['name'],
                    'product_code' => $item['code'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total' => $item['subtotal'],
                ]);
            }

            $customerDetails = "Customer: " . ($fullName ?: $user->name) . "\n"
                . "Phone: " . $cleanPhone . "\n"
                . "Address: " . $fullAddress . ", " . $this->city;

            DeliverySale::create([
                'sale_id' => $sale->id,
                'ecommerce_order_id' => $order->id,
                'delivery_method' => 'Domex',
                'payment_method' => $isCod ? 'Cash on Delivery' : 'Online Payment',
                'delivery_charge' => $this->shipping,
                'status' => 'Processing',
                'customer_details' => $customerDetails,
            ]);

            DB::commit();

            // Send SMS notification to customer
            try {
                \App\Services\SmsService::notifyOrderPlaced($order, $sale);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Failed sending checkout SMS: ' . $e->getMessage());
            }

            // Clear session cart
            session()->forget('cart');
            $this->dispatch('cartUpdated', count: 0);

            // Pass account creation flash info to order confirmation
            if ($accountCreated) {
                session()->flash('new_customer_registered', [
                    'phone' => $cleanPhone,
                    'password' => $defaultPassword,
                ]);
            }

            // Redirect to confirmation page
            return redirect()->route('order-confirmation', ['order_number' => $order->order_number]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Checkout placeOrder error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            $this->dispatch('showToast', message: 'Order failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $seoData = [
            'title' => 'Checkout | Jaffna Gold',
            'robots' => 'noindex, nofollow',
            'canonical' => route('checkout'),
        ];

        return view('livewire.ecommerce.checkout')
            ->layout('components.layouts.webapp', $this->sharedLayoutData($seoData));
    }
}

