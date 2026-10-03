<div>
<div class="page-header">
    <h1>My Account</h1>
    <div class="breadcrumb-jg">
        <span>JAFFNA GOLD</span>
    </div>
</div>

<section class="section-jg section-jg-cream" style="padding-top:1rem;">
    <div class="container-jg">
        @if ($user)
            <div class="account-layout">
                <!-- ===== SIDEBAR ===== -->
                <aside class="account-sidebar reveal visible">
                    <div class="avatar-lg">{{ substr($user->name, 0, 1) }}</div>
                    <div class="name">{{ $user->name }}</div>
                    <div class="email">{{ $user->email }}</div>
                    <div class="email" style="font-size: 0.8rem; margin-top: 0.2rem;"><i class="bi bi-telephone"></i> {{ $user->contact }}</div>
                    <div class="account-nav">
                        <a href="#" data-tab="orders" class="active">Orders</a>
                        <a href="#" data-tab="profile">Profile</a>
                        <a href="#" wire:click.prevent="logout" style="color: var(--danger); margin-top: 1.5rem;"><i class="bi bi-box-arrow-right"></i> Log Out</a>
                    </div>
                </aside>

                <!-- ===== CONTENT ===== -->
                <div class="account-content reveal reveal-delay-1 visible" id="accountContent">
                    <!-- Orders -->
                    <div class="tab-panel active" id="orders">
                        <h3 style="margin-bottom:1.5rem;">Order History</h3>
                        
                        @forelse ($orders as $order)
                            <div class="order-row" style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem; border: 1px solid rgba(201,168,76,0.18); border-radius: 12px; background: var(--white); margin-bottom: 1rem;">
                                <div class="order-info">
                                    <div class="order-num" style="font-family: 'Playfair Display', serif; font-size: 1.2rem; font-weight: bold; color: var(--navy);">{{ $order->order_number }}</div>
                                    <div class="order-meta" style="font-size: 0.8rem; color: var(--body); margin-top: 0.35rem;">
                                        Placed: {{ $order->created_at->format('M d, Y') }} &middot; {{ $order->items->count() }} items &middot; <strong>LKR {{ number_format($order->total_amount, 2) }}</strong>
                                    </div>
                                    
                                    <!-- Order Items Mini List -->
                                    <div class="order-items-mini" style="margin-top: 0.6rem; font-size: 0.76rem; color: var(--charcoal); background: var(--cream); padding: 0.4rem 0.8rem; border-radius: 6px; display: inline-block;">
                                        @foreach ($order->items as $item)
                                            <span style="display: block;">&bull; {{ $item->product ? $item->product->name : 'Product' }} (Qty: {{ $item->quantity }})</span>
                                        @endforeach
                                    </div>
                                </div>
                                <div>
                                    @php
                                        $statusClass = match($order->order_status) {
                                            'pending' => 'processing',
                                            'delivered' => 'delivered',
                                            'cancelled' => 'cancelled',
                                            default => 'in-transit'
                                        };
                                    @endphp
                                    <span class="status-pill {{ $statusClass }}">{{ ucfirst($order->order_status) }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted" style="padding: 4rem 1rem; background: var(--white); border-radius: 12px; border: 1px solid rgba(201,168,76,0.15);">
                                <i class="bi bi-receipt" style="font-size: 2.5rem; display: block; margin-bottom: 0.8rem; color: #cca935;"></i>
                                <h4 class="text-dark">No orders found</h4>
                                <p style="font-size: 0.9rem;">You haven't placed any orders yet. Visit our collections to find handcrafted luxury pieces.</p>
                                <a href="{{ route('shop') }}" class="btn-jg btn-jg-primary btn-jg-sm" style="margin-top: 0.5rem;">Explore Shop</a>
                            </div>
                        @endforelse
                    </div>

                    <!-- Profile -->
                    <div class="tab-panel" id="profile">
                        <h3 style="margin-bottom:1.5rem;">Profile Information</h3>
                        <div class="form-jg" style="max-width:500px;">
                            <div class="form-group" style="margin-bottom: 0.85rem;">
                                <label style="font-weight: 600; display: block; margin-bottom: 0.35rem;">Full Name</label>
                                <input type="text" value="{{ $user->name }}" readonly style="width: 100%; padding: 0.75rem 0.9rem; border: 1px solid rgba(201, 168, 76, 0.24); border-radius: 10px; background: rgba(201,168,76,0.04); color: var(--charcoal); font-size: 0.86rem;">
                            </div>
                            <div class="form-group" style="margin-bottom: 0.85rem;">
                                <label style="font-weight: 600; display: block; margin-bottom: 0.35rem;">Email Address</label>
                                <input type="email" value="{{ $user->email }}" readonly style="width: 100%; padding: 0.75rem 0.9rem; border: 1px solid rgba(201, 168, 76, 0.24); border-radius: 10px; background: rgba(201,168,76,0.04); color: var(--charcoal); font-size: 0.86rem;">
                            </div>
                            <div class="form-group">
                                <label style="font-weight: 600; display: block; margin-bottom: 0.35rem;">Phone Number (Contact)</label>
                                <input type="tel" value="{{ $user->contact }}" readonly style="width: 100%; padding: 0.75rem 0.9rem; border: 1px solid rgba(201, 168, 76, 0.24); border-radius: 10px; background: rgba(201,168,76,0.04); color: var(--charcoal); font-size: 0.86rem;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tabs = document.querySelectorAll('.account-nav a[data-tab]');
        const panels = document.querySelectorAll('.tab-panel');

        tabs.forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                const targetTab = tab.getAttribute('data-tab');

                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');

                panels.forEach(panel => {
                    if (panel.id === targetTab) {
                        panel.classList.add('active');
                    } else {
                        panel.classList.remove('active');
                    }
                });
            });
        });
    });
</script>
</div>
