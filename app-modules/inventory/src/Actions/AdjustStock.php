<?php

declare(strict_types=1);

namespace Vevey\Inventory\Actions;

use Illuminate\Support\Facades\DB;
use Vevey\Inventory\DTOs\AdjustStockData;
use Vevey\Inventory\Enums\ReservationStatus;
use Vevey\Inventory\Enums\StockMovementReason;
use Vevey\Inventory\Events\StockBelowReorderPoint;
use Vevey\Inventory\Models\StockLevel;
use Vevey\Inventory\Models\StockMovement;
use Vevey\Inventory\Models\StockReservation;

/**
 * Physical count / manual adjustment (ADR 2 & 5).
 * Staff enters the counted physical quantity; the service computes delta and appends an `adjustment` movement.
 */
final class AdjustStock
{
    public function handle(AdjustStockData $data): StockLevel
    {
        return DB::transaction(static function () use ($data): StockLevel {
            StockLevel::query()->firstOrCreate(
                ['product_id' => $data->productId],
                ['on_hand' => 0, 'reserved' => 0, 'reorder_point' => $data->reorderPoint ?? 5],
            );

            /** @var StockLevel $level */
            $level = StockLevel::query()
                ->where('product_id', $data->productId)
                ->lockForUpdate()
                ->firstOrFail();

            $delta = $data->countedQty - $level->on_hand;
            $now = now();

            if ($delta !== 0) {
                $movementData = [
                    'product_id' => $data->productId,
                    'qty' => $delta,
                    'reason' => StockMovementReason::Adjustment,
                    'balance_after' => $data->countedQty,
                    'user_id' => $data->userId,
                    'note' => $data->note,
                    'created_at' => $now,
                ];

                if (DB::getDriverName() !== 'pgsql') {
                    $movementData['direction'] = $delta > 0 ? 'in' : 'out';
                }

                StockMovement::query()->create($movementData);
            }

            $activeReserved = (int) StockReservation::query()
                ->where('product_id', $data->productId)
                ->where('status', ReservationStatus::Active)
                ->where('expires_at', '>', $now)
                ->sum('qty');

            $reorderPoint = $data->reorderPoint ?? $level->reorder_point;

            $updates = [
                'on_hand' => $data->countedQty,
                'reserved' => $activeReserved,
                'reorder_point' => $reorderPoint,
            ];
            if (DB::getDriverName() !== 'pgsql') {
                $updates['available'] = $data->countedQty - $activeReserved;
            }

            $level->update($updates);

            if ($data->countedQty <= $reorderPoint) {
                StockBelowReorderPoint::dispatch(
                    productId: $data->productId,
                    onHand: $data->countedQty,
                    reorderPoint: $reorderPoint,
                );
            }

            return $level->refresh();
        });
    }
}
