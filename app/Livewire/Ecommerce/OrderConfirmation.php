<?php

namespace App\Livewire\Ecommerce;

use App\Livewire\Ecommerce\Concerns\InteractsWithStorefront;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Title('Order Confirmation')]
#[Layout('components.layouts.webapp')]
class OrderConfirmation extends Component
{
    use InteractsWithStorefront;

    public $orderNumber;

    public function mount()
    {
        $this->orderNumber = request()->query('order_number', 'N/A');
    }

    public function render()
    {
        $seoData = [
            'title' => 'Order Confirmation | Jaffna Gold',
            'robots' => 'noindex, nofollow',
            'canonical' => route('order-confirmation'),
        ];

        return view('livewire.ecommerce.order-confirmation')
            ->layout('components.layouts.webapp', $this->sharedLayoutData($seoData));
    }
}
