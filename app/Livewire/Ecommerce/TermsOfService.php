<?php

namespace App\Livewire\Ecommerce;

use App\Livewire\Ecommerce\Concerns\InteractsWithStorefront;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Terms of Service | Jaffna Gold')]
#[Layout('components.layouts.webapp')]
class TermsOfService extends Component
{
    use InteractsWithStorefront;

    public function render()
    {
        $seoData = [
            'title' => 'Terms of Service - 22K Gold & Fine Jewelry | Jaffna Gold',
            'description' => 'Review the terms and conditions for purchases, hallmark certification, shipping, and returns at Jaffna Gold.',
            'canonical' => url('/terms-of-service'),
            'og_title' => 'Terms of Service - Jaffna Gold',
            'og_description' => 'Terms of service and customer agreement for Jaffna Gold.',
        ];

        return view('livewire.ecommerce.terms-of-service', $this->sharedLayoutData($seoData));
    }
}
