<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Login - Sistem Manajemen Usaha Terpadu')]
#[Layout('layouts.guest')]
class LoginComponent extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function login(): void
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required|min:4',
        ]);

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        $user = Auth::user();
        if ($user->status !== 'active') {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'Akun anda tidak aktif. Hubungi admin.',
            ]);
        }

        request()->session()->regenerate();

        $this->redirectIntended(route('dashboard'), navigate: false);
    }

    public function render()
    {
        return view('livewire.auth.login-component');
    }
}
