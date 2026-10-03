<div>
<div class="page-header">
    <h1>Checkout</h1>
    <div class="breadcrumb-jg">
        <span>Complete your order securely</span>
    </div>
</div>

<section class="section-jg section-jg-cream" style="padding-top:1rem;">
    <div class="container-jg">
        <form wire:submit.prevent="placeOrder">
            <div class="checkout-layout">
                <!-- ===== LEFT: FORMS ===== -->
                <div class="reveal visible">
                    @auth
                        <div class="alert-jg alert-jg-info" style="margin-bottom: 1.25rem; padding: 0.85rem 1.1rem; border-radius: 10px; background: rgba(201, 168, 76, 0.1); border: 1px solid rgba(201, 168, 76, 0.3); font-size: 0.86rem; color: var(--charcoal); display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <i class="bi bi-person-check-fill" style="color: #b88a2b; margin-right: 0.4rem; font-size: 1.1rem;"></i>
                                <span>Ordering as <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->contact ?: auth()->user()->email }})</span>
                            </div>
                            <span class="badge" style="background: rgba(201,168,76,0.2); color: #8c6819; font-size: 0.75rem; padding: 4px 8px; border-radius: 6px;">Logged In</span>
                        </div>
                    @else
                        <div class="alert-jg alert-jg-success" style="margin-bottom: 1.25rem; padding: 0.85rem 1.1rem; border-radius: 10px; background: rgba(37, 211, 102, 0.08); border: 1px solid rgba(37, 211, 102, 0.25); font-size: 0.86rem; color: #1a6337; display: flex; align-items: center; gap: 0.6rem;">
                            <i class="bi bi-lightning-charge-fill" style="font-size: 1.1rem; color: #1ea952;"></i>
                            <div>
                                <strong>Instant Guest Checkout:</strong> No prior registration needed! Just enter your name & phone number below.
                            </div>
                        </div>
                    @endauth

                    <div class="checkout-form-section">
                        <h3>Customer & Delivery Details</h3>
                        <div class="form-jg">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="full_name">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" id="full_name" placeholder="Enter your full name" wire:model="full_name" style="width: 100%; padding: 0.75rem 0.9rem; border: 1px solid rgba(201, 168, 76, 0.24); border-radius: 10px; background: var(--white); color: var(--charcoal); font-size: 0.86rem;">
                                    @error('full_name') <span class="text-danger" style="font-size: 0.75rem;">{{ $message }}</span> @enderror
                                    @error('first_name') <span class="text-danger" style="font-size: 0.75rem;">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone Number <span class="text-danger">*</span></label>
                                    <input type="tel" id="phone" placeholder="e.g. 077 123 4567" wire:model="phone" style="width: 100%; padding: 0.75rem 0.9rem; border: 1px solid rgba(201, 168, 76, 0.24); border-radius: 10px; background: var(--white); color: var(--charcoal); font-size: 0.86rem;">
                                    @error('phone') <span class="text-danger" style="font-size: 0.75rem;">{{ $message }}</span> @enderror
                                    <small class="text-muted" style="font-size: 0.73rem; display: block; margin-top: 3px;">Used for delivery updates & login.</small>
                                </div>
                            </div>

                            <div class="form-group" style="margin-top: 0.75rem;">
                                <label for="address">Delivery Address <span class="text-danger">*</span></label>
                                <textarea id="address" placeholder="Street name, house/building number, landmark" wire:model="address" rows="2" style="width: 100%; padding: 0.75rem 0.9rem; border: 1px solid rgba(201, 168, 76, 0.24); border-radius: 10px; background: var(--white); color: var(--charcoal); font-size: 0.86rem; font-family: inherit; resize: vertical;"></textarea>
                                @error('address') <span class="text-danger" style="font-size: 0.75rem;">{{ $message }}</span> @enderror
                                @error('address1') <span class="text-danger" style="font-size: 0.75rem;">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group" style="margin-top: 0.75rem; position: relative;">
                                <label for="city">City / Town <span class="text-danger">*</span></label>
                                <div style="position: relative;">
                                    <input type="text" id="city" 
                                           placeholder="Type city (e.g. Jaffna, Colombo, Kandy...)" 
                                           wire:model.live.debounce.150ms="city" 
                                           autocomplete="off" 
                                           style="width: 100%; padding: 0.75rem 0.9rem; border: 1px solid rgba(201, 168, 76, 0.24); border-radius: 10px; background: var(--white); color: var(--charcoal); font-size: 0.86rem;">
                                    @if(strlen(trim($city)) > 0)
                                        <button type="button" wire:click="$set('city', ''); $set('showCitySuggestions', false);" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #9ca3af; cursor: pointer; font-size: 0.9rem;">
                                            <i class="bi bi-x-circle-fill"></i>
                                        </button>
                                    @endif
                                </div>
                                @error('city') <span class="text-danger" style="font-size: 0.75rem;">{{ $message }}</span> @enderror

                                <!-- City Suggestions Dropdown -->
                                @if($showCitySuggestions && count($citySuggestions) > 0)
                                    <div class="city-suggestions-dropdown" style="position: absolute; top: 100%; left: 0; right: 0; z-index: 1000; background: #ffffff; border: 1px solid rgba(201,168,76,0.35); border-radius: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.12); margin-top: 4px; max-height: 220px; overflow-y: auto;">
                                        <div style="padding: 6px 12px; font-size: 0.7rem; color: #8c7662; font-weight: 700; background: #faf7f2; border-bottom: 1px solid rgba(201,168,76,0.15); text-transform: uppercase; letter-spacing: 0.5px;">
                                            <i class="bi bi-geo-alt-fill text-warning"></i> Matching Cities (Tap to select)
                                        </div>
                                        @foreach($citySuggestions as $suggestion)
                                            <div wire:click="selectCity('{{ $suggestion }}')" 
                                                 style="padding: 8px 14px; font-size: 0.86rem; color: var(--charcoal); cursor: pointer; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f3f4f6;"
                                                 onmouseover="this.style.background='#fdfbf7'" 
                                                 onmouseout="this.style.background='#fff'">
                                                <span><i class="bi bi-pin-map text-muted" style="margin-right: 6px;"></i> <strong>{{ $suggestion }}</strong></span>
                                                <span class="badge" style="background: rgba(201,168,76,0.15); color: #8c6819; font-size: 0.7rem; padding: 3px 8px; border-radius: 6px;">Select</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="checkout-form-section" style="margin-top: 1.5rem;">
                        <h3>Payment Method</h3>
                        <div class="form-jg">
                            <div class="form-group">
                                <label for="payment_method">Select Payment <span class="text-danger">*</span></label>
                                <select id="payment_method" wire:model.live="payment_method" style="width: 100%; padding: 0.75rem 0.9rem; border: 1px solid rgba(201, 168, 76, 0.24); border-radius: 10px; background: var(--white); color: var(--charcoal); font-size: 0.86rem;">
                                    <option value="COD">Cash On Delivery (COD)</option>
                                    <option value="Bank Transfer">Bank Transfer</option>
                                    <option value="Card">Credit/Debit Card</option>
                                </select>
                            </div>

                            @if ($payment_method === 'Card')
                                <div class="card-details-section" style="margin-top: 1rem; padding: 1rem; border: 1px solid rgba(201,168,76,0.18); border-radius: 10px; background: rgba(201,168,76,0.05);">
                                    <div class="form-group">
                                        <label>Card Number</label>
                                        <input type="text" placeholder="4242 4242 4242 4242" wire:model="card_number" style="width: 100%; padding: 0.75rem 0.9rem; border: 1px solid rgba(201, 168, 76, 0.24); border-radius: 10px; background: var(--white); color: var(--charcoal); font-size: 0.86rem;">
                                        @error('card_number') <span class="text-danger" style="font-size: 0.75rem;">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="form-row" style="margin-top: 0.75rem;">
                                        <div class="form-group">
                                            <label>Expiry (MM/YY)</label>
                                            <input type="text" placeholder="12/28" wire:model="card_expiry" style="width: 100%; padding: 0.75rem 0.9rem; border: 1px solid rgba(201, 168, 76, 0.24); border-radius: 10px; background: var(--white); color: var(--charcoal); font-size: 0.86rem;">
                                            @error('card_expiry') <span class="text-danger" style="font-size: 0.75rem;">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="form-group">
                                            <label>CVV</label>
                                            <input type="text" placeholder="123" wire:model="card_cvv" style="width: 100%; padding: 0.75rem 0.9rem; border: 1px solid rgba(201, 168, 76, 0.24); border-radius: 10px; background: var(--white); color: var(--charcoal); font-size: 0.86rem;">
                                            @error('card_cvv') <span class="text-danger" style="font-size: 0.75rem;">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="form-group" style="margin-top: 0.75rem;">
                                        <label>Name on Card</label>
                                        <input type="text" placeholder="John Doe" wire:model="card_name" style="width: 100%; padding: 0.75rem 0.9rem; border: 1px solid rgba(201, 168, 76, 0.24); border-radius: 10px; background: var(--white); color: var(--charcoal); font-size: 0.86rem;">
                                        @error('card_name') <span class="text-danger" style="font-size: 0.75rem;">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            @elseif ($payment_method === 'Bank Transfer')
                                <div class="bank-transfer-details" style="margin-top: 1rem; padding: 1rem; border: 1px solid rgba(201,168,76,0.18); border-radius: 10px; background: rgba(201,168,76,0.05); font-size: 0.85rem; line-height: 1.6; color: var(--charcoal);">
                                    <strong>Bank Account Details:</strong><br>
                                    Bank: Commercial Bank of Ceylon<br>
                                    Branch: Jaffna Main Branch<br>
                                    Account Name: Jaffna Gold (Pvt) Ltd<br>
                                    Account Number: 1000234567<br>
                                    <span class="text-muted" style="font-size: 0.8rem;">*Please transfer the exact total and upload or send your transfer slip to our WhatsApp support (+94 21 222 2222) referencing your order number.</span>
                                </div>
                            @endif

                            <div class="form-group" style="margin-top: 1rem;">
                                <label for="notes">Delivery Notes (Optional)</label>
                                <textarea id="notes" placeholder="Any special delivery instructions or directions." wire:model="notes" rows="2" style="width: 100%; padding: 0.75rem 0.9rem; border: 1px solid rgba(201, 168, 76, 0.24); border-radius: 10px; background: var(--white); color: var(--charcoal); font-size: 0.86rem; font-family: inherit;"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ===== RIGHT: ORDER SUMMARY ===== -->
                <div class="order-summary reveal reveal-delay-1 visible">
                    <h3>Order Summary</h3>
                    
                    <div class="order-items-summary" style="margin-bottom: 1.5rem; max-height: 280px; overflow-y: auto; padding-right: 0.25rem;">
                        @foreach ($cartItems as $item)
                            <div class="item-summary-row" style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-bottom: 1px solid rgba(201,168,76,0.1); font-size: 0.85rem;">
                                <div style="flex: 1; padding-right: 1rem;">
                                    <strong>{{ $item['name'] }}</strong>
                                    <div class="text-muted" style="font-size: 0.75rem;">Qty: {{ $item['quantity'] }} @ LKR {{ number_format($item['price'], 2) }}</div>
                                </div>
                                <div style="text-align: right; font-weight: 500;">
                                    LKR {{ number_format($item['line_total'] ?? $item['subtotal'] ?? 0, 2) }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="row">
                        <span>Subtotal</span>
                        <span>LKR {{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div class="row">
                        <span>Shipping</span>
                        <span style="color:var(--accent);">Free</span>
                    </div>
                    <div class="row total">
                        <span>Total</span>
                        <span>LKR {{ number_format($total, 2) }}</span>
                    </div>
                    <div style="margin-top:1rem;padding:0.75rem;background:var(--cream);border-radius:8px;font-size:0.8rem;color:var(--body);text-align:center;">
                        <i class="bi bi-lock-fill"></i> Your payment information is secure
                    </div>
                    <button type="submit" class="btn-jg btn-jg-primary" style="width: 100%; border: none; margin-top: 1rem;">Place Order</button>
                </div>
            </div>
        </form>
    </div>
</section>
</div>