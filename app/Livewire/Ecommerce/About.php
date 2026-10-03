<?php

namespace App\Livewire\Ecommerce;

use App\Livewire\Ecommerce\Concerns\InteractsWithStorefront;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Title('Our World')]
#[Layout('components.layouts.webapp')]
class About extends Component
{
    use InteractsWithStorefront;

    public function render()
    {
        $seoData = [
            'title' => 'Our Story & Goldsmithing Heritage | Jaffna Gold',
            'description' => 'Discover Jaffna Gold\'s 3-generation legacy of master goldsmithing, artisan craftsmanship, and sustainable fine jewelry making in Jaffna, Sri Lanka.',
            'keywords' => 'about Jaffna Gold, goldsmiths Jaffna, 3 generations gold artisan, handcrafted jewelry story',
            'canonical' => route('about'),
        ];

        return view('livewire.ecommerce.about')
            ->layout('components.layouts.webapp', $this->sharedLayoutData($seoData));
    }
}
