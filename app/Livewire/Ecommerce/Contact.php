<?php

namespace App\Livewire\Ecommerce;

use App\Livewire\Ecommerce\Concerns\InteractsWithStorefront;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Title('Contact')]
#[Layout('components.layouts.webapp')]
class Contact extends Component
{
    use InteractsWithStorefront;

    public $first_name = '';
    public $last_name = '';
    public $email = '';
    public $message = '';

    protected $rules = [
        'first_name' => 'required',
        'last_name' => 'required',
        'email' => 'required|email',
        'message' => 'required',
    ];

    public function sendMessage()
    {
        $this->validate();

        // Simulate sending contact message
        $this->reset(['first_name', 'last_name', 'email', 'message']);
        $this->dispatch('showToast', message: 'Message sent! We will contact you shortly.');
    }

    public function render()
    {
        $seoData = [
            'title' => 'Contact Us & Boutique Inquiries | Jaffna Gold',
            'description' => 'Get in touch with Jaffna Gold for custom jewelry requests, boutique visits, or customer support in Jaffna, Sri Lanka.',
            'keywords' => 'contact Jaffna Gold, custom gold design Sri Lanka, jewelry customer support',
            'canonical' => route('contact'),
        ];

        return view('livewire.ecommerce.contact')
            ->layout('components.layouts.webapp', $this->sharedLayoutData($seoData));
    }
}
