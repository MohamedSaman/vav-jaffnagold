<?php

namespace App\Livewire\Ecommerce;

use App\Livewire\Ecommerce\Concerns\InteractsWithStorefront;
use App\Models\EcommerceOrder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Title('Account')]
#[Layout('components.layouts.webapp')]
class Account extends Component
{
    use InteractsWithStorefront;

    public $orders = [];
    public $user = null;

    public function mount()
    {
        if (!Auth::check()) {
            return $this->redirect(route('login'));
        }

        $this->user = Auth::user();
        $this->loadOrders();
    }

    public function loadOrders()
    {
        $email = $this->user->email;
        $phone = $this->user->contact;

        $this->orders = EcommerceOrder::query()
            ->with('items.product')
            ->where(function ($query) use ($email, $phone) {
                if ($email) {
                    $query->where('customer_email', $email);
                }
                if ($phone) {
                    $query->orWhere('customer_phone', $phone);
                }
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function logout()
    {
        Auth::guard('web')->logout();
        session()->invalidate();
        session()->regenerateToken();

        $this->dispatch('cartUpdated'); // Force navigation reload to clear header profile states
        return $this->redirect(route('home'));
    }

    public function render()
    {
        $seoData = [
            'title' => 'My Account | Jaffna Gold',
            'robots' => 'noindex, nofollow',
            'canonical' => route('account'),
        ];

        return view('livewire.ecommerce.account')
            ->layout('components.layouts.webapp', $this->sharedLayoutData($seoData));
    }
}
