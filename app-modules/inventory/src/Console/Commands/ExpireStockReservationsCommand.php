<?php

declare(strict_types=1);

namespace Vevey\Inventory\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Vevey\Inventory\Enums\ReservationStatus;
use Vevey\Inventory\Models\StockLevel;
use Vevey\Inventory\Models\StockReservation;

final class ExpireStockReservationsCommand extends Command
{
    protected $signature = 'stock:expire-reservations';

    protected $description = 'Marks expired active stock reservations as released and updates stock_levels.reserved (ADR 2).';

    public function handle(): int
    {
        $now = now();

        $expiredProductIds = StockReservation::query()
            ->where('status', ReservationStatus::Active)
            ->where('expires_at', '<=', $now)
            ->distinct()
            ->pluck('product_id');

        $released = StockReservation::query()
            ->where('status', ReservationStatus::Active)
            ->where('expires_at', '<=', $now)
            ->update(['status' => ReservationStatus::Released]);

        foreach ($expiredProductIds as $productId) {
            $activeReserved = (int) StockReservation::query()
                ->where('product_id', $productId)
                ->where('status', ReservationStatus::Active)
                ->where('expires_at', '>', $now)
                ->sum('qty');

            $level = StockLevel::query()->find($productId);
            if ($level instanceof StockLevel) {
                $updates = ['reserved' => $activeReserved];
                if (DB::getDriverName() !== 'pgsql') {
                    $updates['available'] = $level->on_hand - $activeReserved;
                }
                $level->update($updates);
            }
        }

        $this->info("Released {$released} expired reservation(s).");

        return self::SUCCESS;
    }
}
