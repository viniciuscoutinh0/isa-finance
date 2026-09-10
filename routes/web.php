<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\LogoutController;
use App\Livewire\Accounts\Index as AccountsIndex;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Categories\Index as CategoriesIndex;
use App\Livewire\Dashboard;
use App\Livewire\Transactions\Index as TransactionsIndex;
use App\Livewire\Transfers\Index as TransfersIndex;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/painel')->name('home');

Route::middleware('guest')->group(function (): void {
    Route::livewire('/entrar', Login::class)->name('login');
    Route::livewire('/criar-conta', Register::class)->name('register');
    Route::livewire('/esqueci-a-senha', ForgotPassword::class)->name('password.request');
    Route::livewire('/redefinir-senha/{token}', ResetPassword::class)->name('password.reset');
});

Route::middleware('auth')->group(function (): void {
    Route::livewire('/painel', Dashboard::class)->name('dashboard');

    Route::livewire('/contas', AccountsIndex::class)->name('accounts.index');
    Route::livewire('/lancamentos', TransactionsIndex::class)->name('transactions.index');
    Route::livewire('/transferencias', TransfersIndex::class)->name('transfers.index');
    Route::livewire('/categorias', CategoriesIndex::class)->name('categories.index');

    Route::post('/sair', LogoutController::class)->name('logout');
});
