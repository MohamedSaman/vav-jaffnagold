<?php

namespace App\Livewire\Ecommerce;

use App\Livewire\Ecommerce\Concerns\InteractsWithStorefront;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Title('Custom Products')]
#[Layout('components.layouts.webapp')]
class Journal extends Component
{
    use InteractsWithStorefront;

    public function render()
    {
        $seoData = [
            'title' => 'Customize Your Own Jewelry | Jaffna Gold',
            'description' => 'Request custom 22K gold or sterling silver jewelry designed specifically for you by Jaffna Gold master artisans.',
            'keywords' => 'custom jewelry Sri Lanka, bespoke gold design, custom gold ring, custom necklace Jaffna',
            'canonical' => route('journal'),
        ];

        return view('livewire.ecommerce.journal')
            ->layout('components.layouts.webapp', $this->sharedLayoutData($seoData));
    }
}
