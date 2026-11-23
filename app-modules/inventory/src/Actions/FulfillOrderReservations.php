<?php

declare(strict_types=1);

namespace Vevey\Inventory\Actions;

use Illuminate\Support\Facades\DB;
use Vevey\Inventory\Enums\ReservationStatus;
use Vevey\Inventory\Enums\StockMovementReason;
use Vevey\Inventory\Events\StockBelowReorderPoint;
use Vevey\Inventory\Models\StockLevel;
use Vevey\Inventory\Models\StockMovement;
use Vevey\Inventory\Models\StockReservation;

/**
 * Converts active reservations for a paid order into `sale` ledger entries (ADR 2).
 * Idempotent: if a `sale` movement already exists for (orders, orderId, productId), it is skipped.
 */
final class FulfillOrderReservations
{
    public function handle(string $orderId, ?string $userId = null, ?string $note = null): void
    {
        DB::transaction(static function () use ($orderId, $userId, $note): void {
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

            $levels = StockLevel::query()
                ->whereIn('product_id', $productIds)
                ->orderBy('product_id')
                ->lockForUpdate()
                ->get()
                ->keyBy('product_id');

            $now = now();

            foreach ($reservations as $reservation) {
                $alreadyRecorded = StockMovement::query()
                    ->where('reference_type', 'orders')
                    ->where('reference_id', $orderId)
                    ->where('reason', StockMovementReason::Sale)
                    ->where('product_id', $reservation->product_id)
                    ->exists();

                $reservation->update(['status' => ReservationStatus::Fulfilled]);

                /** @var StockLevel $level */
                $level = $levels->get($reservation->product_id)
                    ?? StockLevel::query()->create([
                        'product_id' => $reservation->product_id,
                        'on_hand' => 0,
                        'reserved' => 0,
                        'reorder_point' => 5,
                    ]);

                $newOnHand = $level->on_hand;

                if (! $alreadyRecorded) {
                    $newOnHand = $level->on_hand - $reservation->qty;

                    $movementData = [
                        'product_id' => $reservation->product_id,
                        'qty' => -$reservation->qty,
                        'reason' => StockMovementReason::Sale,
                        'reference_type' => 'orders',
                        'reference_id' => $orderId,
                        'balance_after' => $newOnHand,
                        'user_id' => $userId,
                        'note' => $note ?? 'Baixa por pagamento confirmado.',
                        'created_at' => $now,
                    ];

                    if (DB::getDriverName() !== 'pgsql') {
                        $movementData['direction'] = 'out';
                    }

                    StockMovement::query()->create($movementData);
                }

                $activeReserved = (int) StockReservation::query()
                    ->where('product_id', $reservation->product_id)
                    ->where('status', ReservationStatus::Active)
                    ->where('expires_at', '>', $now)
                    ->sum('qty');

                $updates = [
                    'on_hand' => $newOnHand,
                    'reserved' => $activeReserved,
                ];
                if (DB::getDriverName() !== 'pgsql') {
                    $updates['available'] = $newOnHand - $activeReserved;
                }

                $level->update($updates);

                if ($newOnHand <= $level->reorder_point) {
                    StockBelowReorderPoint::dispatch(
                        productId: $level->product_id,
                        onHand: $newOnHand,
                        reorderPoint: $level->reorder_point,
                    );
                }
            }
        });
    }
}
