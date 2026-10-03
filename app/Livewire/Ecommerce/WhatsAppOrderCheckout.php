<?php

namespace App\Livewire\Ecommerce;

use App\Models\Customer;
use App\Models\DeliverySale;
use App\Models\ProductStock;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Models\WhatsAppOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Confirm Your Order | Jaffna Gold')]
#[Layout('components.layouts.webapp')]
class WhatsAppOrderCheckout extends Component
{
    use WithFileUploads;

    public $token;

    public $whatsappOrder = null;

    public $isInvalid = false;

    public $isExpired = false;

    public $isCompleted = false;

    // Customer Form Inputs
    public $customer_name = '';

    public $customer_phone = '';

    public $customer_whatsapp = '';

    public $address_line1 = '';

    public $address_line2 = '';

    public $city = '';

    public $district = '';

    public $postal_code = '';

    public $delivery_method = 'Domex'; // 'Domex', 'Post'

    public $payment_method = 'Cash on Delivery'; // 'Cash on Delivery', 'Bank Transfer', 'Online Card'

    public $bank_slip = null;

    public $customer_notes = '';

    // Success State
    public $orderConfirmed = false;

    public $createdSale = null;

    protected $rules = [
        'customer_name' => 'required|string|min:2|max:150',
        'customer_phone' => 'required|string|min:9|max:20',
        'address_line1' => 'required|string|min:5|max:255',
        'city' => 'required|string|min:2|max:100',
        'delivery_method' => 'nullable|string',
        'payment_method' => 'required|string|in:Cash on Delivery,Bank Transfer,Online Card',
        'bank_slip' => 'nullable|image|max:5120', // Max 5MB
    ];

    protected $messages = [
        'customer_name.required' => 'Please enter your full name.',
        'customer_phone.required' => 'Please enter your contact phone number.',
        'address_line1.required' => 'Please provide your street / delivery address.',
        'city.required' => 'Please enter your city or nearest town.',
    ];

    public function mount($token)
    {
        $this->token = $token;
        $order = WhatsAppOrder::where('token', $token)->first();

        if (! $order) {
            $this->isInvalid = true;

            return;
        }

        $this->whatsappOrder = $order;

        if ($order->status === 'completed' && $order->sale_id) {
            $this->isCompleted = true;
            $this->createdSale = Sale::with(['deliverySale', 'items'])->find($order->sale_id);
            $this->orderConfirmed = true;

            return;
        }

        if ($order->expires_at && $order->expires_at->isPast()) {
            $this->isExpired = true;

            return;
        }

        // Prefill if customer details are already attached to order
        $this->customer_name = $order->customer_name ?? '';
        $this->customer_phone = $order->customer_phone ?? '';
        $this->customer_whatsapp = $order->customer_phone ?? '';
    }

    public function submitOrder()
    {
        if ($this->isInvalid || $this->isExpired || $this->orderConfirmed) {
            return;
        }

        $this->validate();

        try {
            DB::beginTransaction();

            $order = WhatsAppOrder::where('token', $this->token)
                ->lockForUpdate()
                ->firstOrFail();

            if ($order->status === 'completed') {
                DB::rollBack();
                $this->orderConfirmed = true;
                $this->createdSale = Sale::with(['deliverySale', 'items'])->find($order->sale_id);

                return;
            }

            $cleanPhone = preg_replace('/[^0-9+]/', '', $this->customer_phone);
            $cleanWhatsapp = $this->customer_whatsapp ? preg_replace('/[^0-9+]/', '', $this->customer_whatsapp) : $cleanPhone;
            $fullAddress = trim($this->address_line1.($this->address_line2 ? ', '.$this->address_line2 : '').($this->district ? ', '.$this->district : '').($this->postal_code ? ' ('.$this->postal_code.')' : ''));

            // 1. Find or create Customer profile
            $customer = Customer::where('phone', $cleanPhone)
                ->orWhere('phone', ltrim($cleanPhone, '+'))
                ->first();

            if (! $customer) {
                $customer = Customer::create([
                    'name' => trim($this->customer_name),
                    'phone' => $cleanPhone,
                    'type' => 'retail',
                    'address' => $fullAddress.', '.$this->city,
                ]);
            }

            // 2. Handle bank slip upload if provided
            $slipPath = null;
            if ($this->bank_slip) {
                $slipPath = $this->bank_slip->store('delivery_slips', 'public');
            }

            // 3. Create Sale record
            $creatorId = $order->created_by;
            if (! $creatorId) {
                $creatorId = User::where('role', 'delivery_man')->value('id') ?: 1;
            }

            $isCod = ($this->payment_method === 'Cash on Delivery');
            $saleTotal = (float) $order->total_amount;
            $dueAmount = $isCod ? $saleTotal : 0;
            $paymentStatus = $isCod ? 'pending' : 'paid';

            $sale = Sale::create([
                'sale_id' => Sale::generateSaleId(),
                'invoice_number' => Sale::generateInvoiceNumber(),
                'customer_id' => $customer->id,
                'walking_customer_name' => trim($this->customer_name),
                'walking_customer_phone' => $cleanPhone,
                'customer_type' => 'retail',
                'subtotal' => $order->subtotal,
                'discount_amount' => (float) ($order->discount_amount ?? 0),
                'total_amount' => $saleTotal,
                'due_amount' => $dueAmount,
                'payment_type' => $dueAmount > 0 ? 'partial' : 'full',
                'payment_status' => $paymentStatus,
                'status' => 'confirm',
                'sale_type' => 'staff',
                'order_source' => 'whatsapp',
                'user_id' => $creatorId,
                'delivery_status' => 'pending', // Pending Pack
                'notes' => "WhatsApp Order link ({$order->token}) | ".($this->customer_notes ? 'Notes: '.$this->customer_notes : ''),
            ]);

            // 4. Create Sale Items & Deduct Stock
            if (is_array($order->items)) {
                foreach ($order->items as $it) {
                    $productId = $it['product_id'] ?? null;
                    $qty = (int) ($it['quantity'] ?? 1);
                    $unitPrice = (float) ($it['unit_price'] ?? 0);
                    $itemDiscount = (float) ($it['discount'] ?? 0);
                    $itemSubtotal = (float) ($it['subtotal'] ?? (max(0, ($unitPrice - $itemDiscount)) * $qty));

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $productId,
                        'product_name' => $it['name'] ?? 'Jewelry Item',
                        'product_code' => $it['code'] ?? '',
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'discount_per_unit' => $itemDiscount,
                        'total_discount' => $itemDiscount * $qty,
                        'total' => $itemSubtotal,
                    ]);

                    // Deduct inventory
                    if ($productId) {
                        $stock = ProductStock::where('product_id', $productId)->first();
                        if ($stock) {
                            $stock->available_stock = max(0, $stock->available_stock - $qty);
                            $stock->sold_count += $qty;
                            $stock->total_stock = $stock->available_stock + $stock->damage_stock;
                            $stock->save();
                        }
                    }
                }
            }

            // 5. Create DeliverySale record with unique Barcode
            $customerDetails = 'Customer: '.trim($this->customer_name)."\n"
                .'Phone: '.$cleanPhone.($cleanWhatsapp !== $cleanPhone ? " (WA: {$cleanWhatsapp})" : '')."\n"
                .'Address: '.$fullAddress.', '.$this->city;

            $deliveryPaymentMethod = ($this->payment_method === 'Cash on Delivery') ? 'Cash on Delivery' : 'Online Payment';

            $deliverySale = DeliverySale::create([
                'sale_id' => $sale->id,
                'delivery_method' => $this->delivery_method,
                'payment_method' => $deliveryPaymentMethod,
                'delivery_charge' => $order->delivery_charge,
                'status' => 'Processing', // Pending Pack
                'customer_details' => $customerDetails,
                'slip_image' => $slipPath,
            ]);

            // 6. Update WhatsApp Order status
            $order->update([
                'status' => 'completed',
                'sale_id' => $sale->id,
                'customer_name' => trim($this->customer_name),
                'customer_phone' => $cleanPhone,
            ]);

            DB::commit();

            // Notify Customer via SMS that order is confirmed
            try {
                $sale->load(['customer', 'deliverySale']);
                \App\Services\SmsService::notifyOrderConfirmed($sale);
            } catch (\Throwable $e) {
                Log::error('[WhatsAppOrderCheckout] Failed to send order confirmed SMS: '.$e->getMessage());
            }

            $this->createdSale = $sale->load(['deliverySale', 'items']);
            $this->orderConfirmed = true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error submitting WhatsApp order: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            session()->flash('error', 'We encountered an error processing your order: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.ecommerce.whatsapp-order-checkout')
            ->layout('components.layouts.webapp', [
                'title' => 'Confirm Your Delivery Order | Jaffna Gold',
                'metaDescription' => 'Review your order items and confirm your parcel delivery address with Jaffna Gold Covering.',
            ]);
    }
}
