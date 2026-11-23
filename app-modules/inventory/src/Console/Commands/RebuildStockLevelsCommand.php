<?php

declare(strict_types=1);

namespace Vevey\Inventory\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Vevey\Inventory\Enums\ReservationStatus;
use Vevey\Inventory\Models\StockLevel;
use Vevey\Inventory\Models\StockMovement;
use Vevey\Inventory\Models\StockReservation;

final class RebuildStockLevelsCommand extends Command
{
    protected $signature = 'stock:rebuild';

    protected $description = 'Recomputes stock_levels cache from stock_movements ledger and active reservations (ADR 2).';

    public function handle(): int
    {
        $now = now();

        $movementSums = StockMovement::query()
            ->selectRaw('product_id, sum(qty) as on_hand')
            ->groupBy('product_id')
            ->pluck('on_hand', 'product_id');

        $reservedSums = StockReservation::query()
            ->where('status', ReservationStatus::Active)
            ->where('expires_at', '>', $now)
            ->selectRaw('product_id, sum(qty) as reserved')
            ->groupBy('product_id')
            ->pluck('reserved', 'product_id');

        $productIds = StockLevel::query()->pluck('product_id')
            ->merge($movementSums->keys())
            ->merge($reservedSums->keys())
            ->unique();

        $count = 0;

        foreach ($productIds as $productId) {
            $onHand = (int) ($movementSums->get($productId) ?? 0);
            $reserved = (int) ($reservedSums->get($productId) ?? 0);

            $values = [
                'on_hand' => $onHand,
                'reserved' => $reserved,
            ];

            if (DB::getDriverName() !== 'pgsql') {
                $values['available'] = $onHand - $reserved;
            }

            StockLevel::query()->updateOrCreate(
                ['product_id' => (string) $productId],
                $values,
            );
            $count++;
        }

        $this->info("Rebuilt {$count} stock level(s) from ledger.");

        return self::SUCCESS;
    }
}
