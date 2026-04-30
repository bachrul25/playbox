<?php

use App\Livewire\Auth\LoginComponent;
use App\Livewire\CashflowComponent;
use App\Livewire\CategoryComponent;
use App\Livewire\DashboardComponent;
use App\Livewire\ExpenseComponent;
use App\Livewire\FinanceCategoryComponent;
use App\Livewire\FinanceComponent;
use App\Livewire\IncomeComponent;
use App\Livewire\PaymentMethodComponent;
use App\Livewire\PosComponent;
use App\Livewire\ProductComponent;
use App\Livewire\RentalComponent;
use App\Livewire\RentalHistoryComponent;
use App\Livewire\RentalUnitComponent;
use App\Livewire\ReportComponent;
use App\Livewire\TransactionHistoryComponent;
use App\Livewire\UserManagementComponent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::get('/login', LoginComponent::class)->name('login');

Route::post('/logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();

    return redirect()->route('login');
})->name('logout');

Route::middleware(['auth'])->group(function () {
    // Dashboard - all roles
    Route::get('/dashboard', DashboardComponent::class)->name('dashboard');

    // POS - admin & kasir (history visible to owner too)
    Route::middleware('role:admin,kasir')->group(function () {
        Route::get('/pos', PosComponent::class)->name('pos');
    });
    Route::middleware('role:admin,kasir,owner')->group(function () {
        Route::get('/transactions', TransactionHistoryComponent::class)->name('transactions');
    });

    // Rental - admin & operator (history for owner too)
    Route::middleware('role:admin,operator')->group(function () {
        Route::get('/rental', RentalComponent::class)->name('rental');
    });
    Route::middleware('role:admin,operator,owner')->group(function () {
        Route::get('/rental/history', RentalHistoryComponent::class)->name('rental.history');
    });

    // Master data - admin & owner
    Route::middleware('role:admin,owner')->group(function () {
        Route::get('/categories', CategoryComponent::class)->name('categories');
        Route::get('/products', ProductComponent::class)->name('products');
        Route::get('/rental-units', RentalUnitComponent::class)->name('rental.units');
        Route::get('/finance-categories', FinanceCategoryComponent::class)->name('finance.categories');
        Route::get('/payment-methods', PaymentMethodComponent::class)->name('payment.methods');

        Route::get('/finance', FinanceComponent::class)->name('finance');
        Route::get('/incomes', IncomeComponent::class)->name('incomes');
        Route::get('/expenses', ExpenseComponent::class)->name('expenses');
        Route::get('/cashflows', CashflowComponent::class)->name('cashflows');

        Route::get('/reports', ReportComponent::class)->name('reports');
    });

    // Admin-only
    Route::middleware('role:admin')->group(function () {
        Route::get('/users', UserManagementComponent::class)->name('users');
    });
});
