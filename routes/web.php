<?php

declare(strict_types=1);

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerAccountController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'home'])->name('store.home');
Route::get('/catalogo', [StorefrontController::class, 'catalog'])->name('store.catalog');
Route::get('/produto/{slug}', [StorefrontController::class, 'product'])->name('store.product');
Route::get('/busca/sugestoes', [StorefrontController::class, 'searchSuggestions'])->name('store.search.suggestions');

Route::get('/checkout', [CheckoutController::class, 'show'])->name('store.checkout');
Route::post('/frete/calcular', [CheckoutController::class, 'quoteShipping'])->name('store.shipping.quote');
Route::post('/checkout/cupom/validar', [CheckoutController::class, 'validateCoupon'])->name('store.checkout.coupon');
Route::post('/checkout', [CheckoutController::class, 'submit'])->name('store.checkout.submit');

// Área do cliente (guard `customers` — ADR 7)
Route::get('/entrar', [CustomerAuthController::class, 'showLogin'])->name('store.login');
Route::post('/entrar', [CustomerAuthController::class, 'login'])->name('store.login.submit');
Route::get('/cadastro', [CustomerAuthController::class, 'showRegister'])->name('store.register');
Route::post('/cadastro', [CustomerAuthController::class, 'register'])->name('store.register.submit');
Route::post('/sair', [CustomerAuthController::class, 'logout'])->name('store.logout');

Route::middleware('auth:customers')->group(static function (): void {
    Route::get('/conta', [CustomerAccountController::class, 'index'])->name('store.account');
    Route::post('/conta/enderecos', [CustomerAccountController::class, 'storeAddress'])->name('store.account.addresses.store');
    Route::delete('/conta/enderecos/{addressId}', [CustomerAccountController::class, 'destroyAddress'])->name('store.account.addresses.destroy');
});
