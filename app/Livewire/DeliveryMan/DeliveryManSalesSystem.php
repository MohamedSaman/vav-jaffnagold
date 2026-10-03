<?php

namespace App\Livewire\DeliveryMan;

use App\Livewire\Admin\SalesSystem;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Title('Create Delivery Sale')]
#[Layout('components.layouts.delivery-man')]
class DeliveryManSalesSystem extends SalesSystem
{
    public function render()
    {
        return view('livewire.delivery-man.delivery-man-sales-system', [
            'subtotal' => $this->subtotal,
            'totalDiscount' => $this->totalDiscount,
            'subtotalAfterItemDiscounts' => $this->subtotalAfterItemDiscounts,
            'additionalDiscountAmount' => $this->additionalDiscountAmount,
            'grandTotal' => $this->grandTotal
        ])->layout('components.layouts.delivery-man');
    }
}
