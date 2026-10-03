<?php

namespace App\Livewire\Ecommerce;

use App\Livewire\Ecommerce\Concerns\InteractsWithStorefront;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Privacy Policy | Jaffna Gold')]
#[Layout('components.layouts.webapp')]
class PrivacyPolicy extends Component
{
    use InteractsWithStorefront;

    public function render()
    {
        $seoData = [
            'title' => 'Privacy Policy - 22K Gold & Fine Jewelry | Jaffna Gold',
            'description' => 'Read Jaffna Gold Privacy Policy. We protect your personal data, payment information, and privacy with security and transparency.',
            'canonical' => url('/privacy-policy'),
            'og_title' => 'Privacy Policy - Jaffna Gold',
            'og_description' => 'Official privacy policy for Jaffna Gold fine jewelry store.',
        ];

        return view('livewire.ecommerce.privacy-policy', $this->sharedLayoutData($seoData));
    }
}
