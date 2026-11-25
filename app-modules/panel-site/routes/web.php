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
Route::get('/favoritos', [StorefrontController::class, 'favorites'])->name('store.favorites');
Route::post('/favoritos/itens', [StorefrontController::class, 'favoriteItems'])
    ->middleware('throttle:30,1')
    ->name('store.favorites.items');
Route::get('/sobre', [StorefrontController::class, 'about'])->name('store.about');
Route::get('/privacidade', [StorefrontController::class, 'privacy'])->name('store.privacy');
Route::get('/trocas-e-devolucoes', [StorefrontController::class, 'returns'])->name('store.returns');
Route::get('/sitemap.xml', [StorefrontController::class, 'sitemap'])->name('store.sitemap');
Route::get('/busca/sugestoes', [StorefrontController::class, 'searchSuggestions'])
    ->middleware('throttle:60,1')
    ->name('store.search.suggestions');

Route::get('/rastrear', [CheckoutController::class, 'showTrack'])->name('store.track');
Route::post('/rastrear', [CheckoutController::class, 'lookupOrder'])
    ->middleware('throttle:15,1')
    ->name('store.track.lookup');

Route::get('/cep/{cep}', [CheckoutController::class, 'lookupCep'])
    ->where('cep', '\d{5}-?\d{3}')
    ->middleware('throttle:30,1')
    ->name('store.cep.lookup');

Route::post('/carrinho/validar', [CheckoutController::class, 'validateCart'])
    ->middleware('throttle:30,1')
    ->name('store.cart.validate');

Route::get('/checkout', [CheckoutController::class, 'show'])->name('store.checkout');
Route::post('/frete/calcular', [CheckoutController::class, 'quoteShipping'])
    ->middleware('throttle:20,1')
    ->name('store.shipping.quote');
Route::post('/checkout/cupom/validar', [CheckoutController::class, 'validateCoupon'])
    ->middleware('throttle:15,1')
    ->name('store.checkout.coupon');
Route::post('/checkout', [CheckoutController::class, 'submit'])
    ->middleware('throttle:10,1')
    ->name('store.checkout.submit');
Route::post('/checkout/pagar/{orderId}', [CheckoutController::class, 'simulatePayment'])
    ->whereUuid('orderId')
    ->middleware(['signed', 'throttle:10,1'])
    ->name('store.checkout.simulate-pay');

// Área do cliente (guard `customers` — ADR 7 + verificação em 2 etapas)
Route::get('/entrar', [CustomerAuthController::class, 'showLogin'])->name('store.login');
Route::post('/entrar', [CustomerAuthController::class, 'login'])
    ->middleware('throttle:10,1')
    ->name('store.login.submit');
Route::get('/cadastro', [CustomerAuthController::class, 'showRegister'])->name('store.register');
Route::post('/cadastro', [CustomerAuthController::class, 'register'])
    ->middleware('throttle:5,1')
    ->name('store.register.submit');
Route::get('/verificar-conta', [CustomerAuthController::class, 'showVerify'])->name('store.verify');
Route::post('/verificar-conta', [CustomerAuthController::class, 'verify'])
    ->middleware('throttle:6,1')
    ->name('store.verify.submit');
Route::post('/verificar-conta/reenviar', [CustomerAuthController::class, 'resendVerification'])
    ->middleware('throttle:3,1')
    ->name('store.verify.resend');
Route::post('/sair', [CustomerAuthController::class, 'logout'])->name('store.logout');

Route::middleware('auth:customers')->group(static function (): void {
    Route::get('/conta', [CustomerAccountController::class, 'index'])->name('store.account');
    Route::put('/conta/perfil', [CustomerAccountController::class, 'updateProfile'])
        ->middleware('throttle:10,1')
        ->name('store.account.profile.update');
    Route::post('/conta/pedidos/{orderId}/cancelar', [CustomerAccountController::class, 'cancelOrder'])
        ->whereUuid('orderId')
        ->middleware('throttle:10,1')
        ->name('store.account.orders.cancel');
    Route::post('/conta/enderecos', [CustomerAccountController::class, 'storeAddress'])
        ->middleware('throttle:15,1')
        ->name('store.account.addresses.store');
    Route::delete('/conta/enderecos/{addressId}', [CustomerAccountController::class, 'destroyAddress'])
        ->whereUuid('addressId')
        ->middleware('throttle:15,1')
        ->name('store.account.addresses.destroy');
});
