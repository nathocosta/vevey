<?php

declare(strict_types=1);

namespace Vevey\Inventory\Actions;

use Illuminate\Support\Facades\DB;
use Vevey\Inventory\Enums\ReservationStatus;
use Vevey\Inventory\Models\StockLevel;
use Vevey\Inventory\Models\StockReservation;

/**
 * Releases active reservations when an unpaid order is cancelled or expires (ADR 2).
 * Does not touch stock_movements.
 */
final class ReleaseOrderReservations
{
    public function handle(string $orderId): void
    {
        DB::transaction(static function () use ($orderId): void {
            $reservations = StockReservation::query()
                ->where('order_id', $orderId)
                ->where('status', ReservationStatus::Active)
                ->orderBy('product_id')
                ->lockForUpdate()
                ->get();

            if ($reservations->isEmpty()) {
                return;
            }

            $productIds = $reservations->pluck('product_id')->unique()->sort()->values()->all();

            StockReservation::query()
                ->whereIn('id', $reservations->pluck('id'))
                ->update(['status' => ReservationStatus::Released]);

            $levels = StockLevel::query()
                ->whereIn('product_id', $productIds)
                ->orderBy('product_id')
                ->lockForUpdate()
                ->get();

            $now = now();

            foreach ($levels as $level) {
                $activeReserved = (int) StockReservation::query()
                    ->where('product_id', $level->product_id)
                    ->where('status', ReservationStatus::Active)
                    ->where('expires_at', '>', $now)
                    ->sum('qty');

                $updates = ['reserved' => $activeReserved];
                if (DB::getDriverName() !== 'pgsql') {
                    $updates['available'] = $level->on_hand - $activeReserved;
                }

                $level->update($updates);
            }
        });
    }
}
