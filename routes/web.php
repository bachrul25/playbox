<?php

use App\Livewire\Auth\LoginComponent;
use App\Livewire\DashboardComponent;
use App\Livewire\ExpenseComponent;
use App\Livewire\PartnerComponent;
use App\Livewire\PartnershipReportComponent;
use App\Livewire\PlayboxComponent;
use App\Livewire\PrivateReportComponent;
use App\Livewire\RentalComponent;
use App\Livewire\UserManagementComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PlayBox Rental — Routing langsung ke Livewire Component (tanpa Controller)
|--------------------------------------------------------------------------
*/

Route::redirect('/', '/dashboard');

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', LoginComponent::class)->name('login');
});

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
})->name('logout');

// Authenticated app
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardComponent::class)->name('dashboard');

    // Admin + Owner
    Route::middleware('role:admin,owner')->group(function () {
        Route::get('/playboxes', PlayboxComponent::class)->name('playboxes');
        Route::get('/partners', PartnerComponent::class)->name('partners');
        Route::get('/expenses', ExpenseComponent::class)->name('expenses');
        Route::get('/reports/private', PrivateReportComponent::class)->name('reports.private');
    });

    // Admin only
    Route::middleware('role:admin')->group(function () {
        Route::get('/rentals', RentalComponent::class)->name('rentals');
        Route::get('/users', UserManagementComponent::class)->name('users');
    });

    // Admin + Owner + Mitra (mitra hanya melihat miliknya, di-handle di komponen)
    Route::middleware('role:admin,owner,mitra')->group(function () {
        Route::get('/reports/partnership', PartnershipReportComponent::class)->name('reports.partnership');
    });
});
