<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Vevey\PanelSite\Http\Controllers\CheckoutController;
use Vevey\PanelSite\Http\Controllers\CustomerAccountController;
use Vevey\PanelSite\Http\Controllers\CustomerAuthController;
use Vevey\PanelSite\Http\Controllers\StorefrontController;

Route::get('/', [StorefrontController::class, 'home'])->name('store.home');
Route::get('/catalogo', [StorefrontController::class, 'catalog'])->name('store.catalog');
Route::get('/produto/{slug}', [StorefrontController::class, 'product'])->name('store.product');
Route::get('/busca/sugestoes', [StorefrontController::class, 'searchSuggestions'])->name('store.search.suggestions');

Route::get('/checkout', [CheckoutController::class, 'show'])->name('store.checkout');
Route::post('/frete/calcular', [CheckoutController::class, 'quoteShipping'])->name('store.shipping.quote');
Route::post('/checkout/cupom/validar', [CheckoutController::class, 'validateCoupon'])->name('store.checkout.coupon');
Route::post('/checkout', [CheckoutController::class, 'submit'])->name('store.checkout.submit');
Route::post('/checkout/pagar/{orderId}', [CheckoutController::class, 'simulatePayment'])->name('store.checkout.simulate-pay');

// Área do cliente (guard `customers` — ADR 7 + verificação em 2 etapas)
Route::get('/entrar', [CustomerAuthController::class, 'showLogin'])->name('store.login');
Route::post('/entrar', [CustomerAuthController::class, 'login'])->name('store.login.submit');
Route::get('/cadastro', [CustomerAuthController::class, 'showRegister'])->name('store.register');
Route::post('/cadastro', [CustomerAuthController::class, 'register'])->name('store.register.submit');
Route::get('/verificar-conta', [CustomerAuthController::class, 'showVerify'])->name('store.verify');
Route::post('/verificar-conta', [CustomerAuthController::class, 'verify'])->name('store.verify.submit');
Route::post('/verificar-conta/reenviar', [CustomerAuthController::class, 'resendVerification'])->name('store.verify.resend');
Route::post('/sair', [CustomerAuthController::class, 'logout'])->name('store.logout');

Route::middleware('auth:customers')->group(static function (): void {
    Route::get('/conta', [CustomerAccountController::class, 'index'])->name('store.account');
    Route::post('/conta/enderecos', [CustomerAccountController::class, 'storeAddress'])->name('store.account.addresses.store');
    Route::delete('/conta/enderecos/{addressId}', [CustomerAccountController::class, 'destroyAddress'])->name('store.account.addresses.destroy');
});
