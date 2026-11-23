<?php

declare(strict_types=1);

namespace Vevey\Inventory\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Vevey\Inventory\Console\Commands\ExpireStockReservationsCommand;
use Vevey\Inventory\Console\Commands\RebuildStockLevelsCommand;
use Vevey\Inventory\Models\StockLevel;
use Vevey\Inventory\Models\StockMovement;
use Vevey\Inventory\Models\StockReservation;

final class InventoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Relation::morphMap([
            'stock_levels' => StockLevel::class,
            'stock_movements' => StockMovement::class,
            'stock_reservations' => StockReservation::class,
        ]);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                RebuildStockLevelsCommand::class,
                ExpireStockReservationsCommand::class,
            ]);
        }
    }
}
