<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Providers;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Vevey\Catalog\Models\Product;
use Vevey\Customers\Models\Customer;
use Vevey\Inventory\Models\StockLevel;
use Vevey\Inventory\Models\StockMovement;
use Vevey\Orders\Models\Order;
use Vevey\PanelAdmin\Filament\Auth\Login;

final class AdminPanelProvider extends PanelProvider
{
    public function boot(): void
    {
        // ADR 8 Rule 5: panels may declare cross-module read relations via resolveRelationUsing
        StockLevel::resolveRelationUsing(
            'product',
            static fn (StockLevel $level): BelongsTo => $level->belongsTo(Product::class, 'product_id'),
        );

        StockMovement::resolveRelationUsing(
            'product',
            static fn (StockMovement $movement): BelongsTo => $movement->belongsTo(Product::class, 'product_id'),
        );

        Customer::resolveRelationUsing(
            'orders',
            static fn (Customer $customer): HasMany => $customer->hasMany(Order::class, 'customer_id')->latest(),
        );
    }
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('Vevey Doces')
            ->brandLogo(asset('images/vevey-logo.svg'))
            ->darkModeBrandLogo(asset('images/vevey-logo-dark.svg'))
            ->brandLogoHeight('2rem')
            ->favicon(asset('favicon.svg'))
            ->login(Login::class)
            ->strictAuthorization()
            ->colors([
                'primary' => Color::Rose,
                'gray' => Color::Slate,
                'warning' => Color::Amber,
                'info' => Color::Violet,
                'success' => Color::Emerald,
                'danger' => Color::Red,
            ])
            ->discoverResources(in: __DIR__ . '/../Filament/Resources', for: 'Vevey\PanelAdmin\Filament\Resources')
            ->discoverPages(in: __DIR__ . '/../Filament/Pages', for: 'Vevey\PanelAdmin\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: __DIR__ . '/../Filament/Widgets', for: 'Vevey\PanelAdmin\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
