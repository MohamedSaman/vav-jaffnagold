<div>
    <style>
        .conf-card {
            background: var(--white);
            border: 1px solid rgba(201, 168, 76, 0.22);
            border-radius: 16px;
            box-shadow: 0 15px 50px rgba(49, 40, 33, 0.08);
            max-width: 650px;
            margin: 2rem auto;
            padding: 3.5rem 2.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .conf-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #cca935, #ebd17b, #cca935);
        }

        .conf-icon-wrap {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(201, 168, 76, 0.12);
            color: #b88a2b;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            margin-bottom: 2rem;
            animation: scaleIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }

        .conf-card h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            font-weight: 600;
            color: var(--navy);
            margin-bottom: 0.8rem;
        }

        .conf-card .sub-text {
            font-size: 0.96rem;
            color: var(--body);
            line-height: 1.7;
            max-width: 480px;
            margin: 0 auto 2.2rem;
        }

        .order-badge-box {
            background: var(--cream);
            border: 1px dashed rgba(201, 168, 76, 0.35);
            border-radius: 12px;
            padding: 1.25rem 2rem;
            display: inline-block;
            margin-bottom: 2.2rem;
        }

        .order-badge-box span {
            display: block;
            font-size: 0.76rem;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: var(--charcoal);
            margin-bottom: 0.35rem;
            opacity: 0.8;
        }

        .order-badge-box strong {
            font-family: 'Playfair Display', serif;
            font-size: 1.65rem;
            color: #b88a2b;
            font-weight: bold;
            letter-spacing: 0.02em;
        }

        .instructions-box {
            background: rgba(201, 168, 76, 0.04);
            border-left: 4px solid #cca935;
            padding: 1.2rem 1.5rem;
            text-align: left;
            border-radius: 0 10px 10px 0;
            margin-bottom: 2.5rem;
            font-size: 0.86rem;
            line-height: 1.6;
            color: var(--charcoal);
        }

        .instructions-box i {
            color: #b88a2b;
            font-size: 1.1rem;
            margin-right: 0.5rem;
        }

        @keyframes scaleIn {
            0% { transform: scale(0.6); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }

        @media (max-width: 640px) {
            .conf-card {
                padding: 2.5rem 1.5rem;
                margin: 1rem;
            }
            .conf-card h1 {
                font-size: 1.8rem;
            }
        }
    </style>

    <div class="page-header" style="background: #302720; padding: 2.5rem 1rem;">
        <h1 style="color: #f9f3ea; margin: 0; font-size: 1.8rem;">Order Placement Complete</h1>
        <div class="breadcrumb-jg" style="color: rgba(249,243,234,0.65); font-size: 0.8rem; margin-top: 0.4rem; letter-spacing: 0.08em; text-transform: uppercase;">
            <span>Handcrafted Heritage Since 2014</span>
        </div>
    </div>

    <section class="section-jg section-jg-cream" style="padding-block: 3rem;">
        <div class="container-jg">
            <div class="conf-card reveal visible">
                <div class="conf-icon-wrap">
                    <i class="bi bi-shield-check"></i>
                </div>
                
                <h1>Thank You for Your Order</h1>
                <p class="sub-text">Your order has been placed successfully. An SMS confirmation with your order reference has been sent to your mobile phone.</p>
                
                <div class="order-badge-box">
                    <span>Your Order Reference</span>
                    <strong>{{ $orderNumber }}</strong>
                </div>

                @if(session('new_customer_registered'))
                    @php
                        $newCust = session('new_customer_registered');
                    @endphp
                    <div style="background: linear-gradient(135deg, rgba(201, 168, 76, 0.12), rgba(201, 168, 76, 0.04)); border: 1.5px solid rgba(201, 168, 76, 0.4); border-radius: 12px; padding: 1.25rem 1.5rem; text-align: left; margin-bottom: 2rem;">
                        <div style="display: flex; align-items: center; gap: 0.6rem; color: #8c6819; font-weight: 700; font-size: 1rem; margin-bottom: 0.5rem;">
                            <i class="bi bi-person-check-fill" style="font-size: 1.3rem;"></i>
                            <span>Account Automatically Created For You!</span>
                        </div>
                        <p style="font-size: 0.85rem; color: var(--charcoal); margin-bottom: 0.75rem;">
                            You are now logged in. In future visits, you can log in directly using your phone number:
                        </p>
                        <div style="display: flex; gap: 1rem; flex-wrap: wrap; background: #fff; padding: 0.75rem 1rem; border-radius: 8px; border: 1px solid rgba(201, 168, 76, 0.25); font-size: 0.85rem;">
                            <div><span class="text-muted">Username / Phone:</span> <strong style="color: var(--navy);">{{ $newCust['phone'] }}</strong></div>
                            <div><span class="text-muted">Default Password:</span> <strong style="color: #b88a2b;">{{ $newCust['password'] }}</strong></div>
                        </div>
                        <small class="text-muted" style="display: block; font-size: 0.75rem; margin-top: 0.5rem;">* You can change your password anytime in your Account settings.</small>
                    </div>
                @endif

                <div class="instructions-box">
                    <div style="font-weight: 600; margin-bottom: 0.4rem;" class="text-dark">
                        <i class="bi bi-info-circle-fill"></i> Next Steps for Bank Transfer
                    </div>
                    If you chose Bank Transfer, please make a payment of your cart total to our bank account and send a screenshot of the payment receipt referencing your order number to our WhatsApp support at <strong style="color:var(--navy);">+94 21 222 2222</strong>.
                </div>

                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <a href="{{ route('home') }}" class="btn-jg btn-jg-outline" style="min-width: 180px; text-align: center;">Return to Home</a>
                    <a href="{{ route('shop') }}" class="btn-jg btn-jg-primary" style="min-width: 180px; text-align: center;">Continue Shopping</a>
                </div>
            </div>
        </div>
    </section>
</div>