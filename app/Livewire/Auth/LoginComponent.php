<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class LoginComponent extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function login()
    {
        $credentials = $this->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if (! Auth::attempt($credentials, $this->remember)) {
            $this->addError('email', 'Email atau password salah.');

            return;
        }

        $user = Auth::user();
        if (! $user->is_active) {
            Auth::logout();
            $this->addError('email', 'Akun Anda nonaktif. Hubungi administrator.');

            return;
        }

        session()->regenerate();

        return $this->redirect(route('dashboard'), navigate: false);
    }

    #[Layout('layouts.guest')]
    #[Title('Login - PlayBox Rental')]
    public function render()
    {
        return view('livewire.auth.login-component');
    }
}
