<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest')]
class CustomLogin extends Component
{
    public $email = ''; // Can be Email or Phone Number

    public $password = '';

    public $remember = false;

    public function render()
    {
        return view('livewire.custom-login');
    }

    public function login()
    {
        $this->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        $input = trim($this->email);
        $cleanPhone = preg_replace('/[^0-9+]/', '', $input);

        // Find the user by Email OR Phone (contact field)
        $user = \App\Models\User::where('email', $input)
            ->orWhere('contact', $input)
            ->when(! empty($cleanPhone), function ($query) use ($cleanPhone) {
                $query->orWhere('contact', $cleanPhone)
                    ->orWhere('contact', ltrim($cleanPhone, '+'))
                    ->orWhere('contact', '0'.substr($cleanPhone, -9))
                    ->orWhere('contact', '+94'.ltrim(substr($cleanPhone, -9), '0'));
            })
            ->first();

        if ($user && \Illuminate\Support\Facades\Hash::check($this->password, $user->password)) {
            // Check if two factor authentication is enabled and confirmed
            $hasTwoFactor = false;
            if (in_array(\Laravel\Fortify\TwoFactorAuthenticatable::class, class_uses_recursive($user))) {
                if (\Laravel\Fortify\Fortify::confirmsTwoFactorAuthentication()) {
                    $hasTwoFactor = ! empty($user->two_factor_secret) && ! is_null($user->two_factor_confirmed_at);
                } else {
                    $hasTwoFactor = ! empty($user->two_factor_secret);
                }
            }

            if ($hasTwoFactor) {
                session()->put([
                    'login.id' => $user->getKey(),
                    'login.remember' => $this->remember,
                ]);

                event(new \Laravel\Fortify\Events\TwoFactorAuthenticationChallenged($user));

                return redirect()->route('two-factor.login');
            }

            // Normal login if 2FA is not enabled
            Auth::login($user, $this->remember);
            session()->regenerate();

            if ($user->role === 'admin') {
                return $this->redirect(route('admin.dashboard'), navigate: false);
            } elseif ($user->role === 'staff') {
                return $this->redirect(route('dashboard'), navigate: false);
            } else {
                return $this->redirectIntended(default: route('dashboard'), navigate: false);
            }
        }

        // When authentication fails
        $this->addError('email', 'These credentials do not match our records.');
        $this->password = '';
    }
}
