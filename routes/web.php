<?php

declare(strict_types=1);

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'home'])->name('store.home');
Route::get('/catalogo', [StorefrontController::class, 'catalog'])->name('store.catalog');
Route::get('/produto/{slug}', [StorefrontController::class, 'product'])->name('store.product');

Route::get('/checkout', [CheckoutController::class, 'show'])->name('store.checkout');
Route::post('/frete/calcular', [CheckoutController::class, 'quoteShipping'])->name('store.shipping.quote');
Route::post('/checkout', [CheckoutController::class, 'submit'])->name('store.checkout.submit');
