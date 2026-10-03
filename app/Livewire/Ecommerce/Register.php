<?php

namespace App\Livewire\Ecommerce;

use App\Actions\Fortify\CreateNewUser;
use App\Livewire\Ecommerce\Concerns\InteractsWithStorefront;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Title('Register')]
#[Layout('components.layouts.webapp')]
class Register extends Component
{
    use InteractsWithStorefront;

    public $name = '';
    public $email = '';
    public $contact = ''; // Phone number
    public $password = '';
    public $password_confirmation = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email',
        'contact' => 'required|string|unique:users,contact',
        'password' => 'required|string|min:8|confirmed',
    ];

    public function register(CreateNewUser $creator)
    {
        $this->validate();

        $user = $creator->create([
            'name' => $this->name,
            'email' => $this->email,
            'contact' => $this->contact,
            'password' => $this->password,
            'password_confirmation' => $this->password_confirmation,
        ]);

        Auth::login($user);
        session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function render()
    {
        $seoData = [
            'title' => 'Register Account | Jaffna Gold',
            'robots' => 'noindex, nofollow',
            'canonical' => route('register'),
        ];

        return view('livewire.ecommerce.register')
            ->layout('components.layouts.webapp', $this->sharedLayoutData($seoData));
    }
}
