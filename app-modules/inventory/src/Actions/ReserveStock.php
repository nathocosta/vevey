<?php

declare(strict_types=1);

namespace Vevey\Inventory\Actions;

use Illuminate\Support\Facades\DB;
use Vevey\Inventory\DTOs\ReserveStockData;
use Vevey\Inventory\Enums\ReservationStatus;
use Vevey\Inventory\Exceptions\InsufficientStockException;
use Vevey\Inventory\Models\StockLevel;
use Vevey\Inventory\Models\StockReservation;

/**
 * Reserves stock at checkout before payment confirmation (ADR 2).
 * Does not touch stock_movements; locks stock_levels ordered by product_id to prevent deadlocks.
 */
final class ReserveStock
{
    public function handle(ReserveStockData $data): void
    {
        if ($data->items === []) {
            return;
        }

        $qtyByProduct = [];
        foreach ($data->items as $item) {
            $qtyByProduct[$item->productId] = ($qtyByProduct[$item->productId] ?? 0) + $item->quantity;
        }

        ksort($qtyByProduct);
        $productIds = array_keys($qtyByProduct);
        $expiresAt = $data->expiresAt ?? now()->toImmutable()->addMinutes(30);

        DB::transaction(static function () use ($data, $qtyByProduct, $productIds, $expiresAt): void {
            $now = now();

            // Ensure a stock_levels row exists for each product, then lock in deterministic product_id order
            foreach ($productIds as $productId) {
                StockLevel::query()->firstOrCreate(
                    ['product_id' => $productId],
                    ['on_hand' => 0, 'reserved' => 0, 'reorder_point' => 5],
                );
            }

            $levels = StockLevel::query()
                ->whereIn('product_id', $productIds)
                ->orderBy('product_id')
                ->lockForUpdate()
                ->get()
                ->keyBy('product_id');

            foreach ($qtyByProduct as $productId => $qty) {
                /** @var StockLevel $level */
                $level = $levels->get($productId);

                // Lazy expiration: only active AND non-expired reservations count
                $activeReserved = (int) StockReservation::query()
                    ->where('product_id', $productId)
                    ->where('status', ReservationStatus::Active)
                    ->where('expires_at', '>', $now)
                    ->sum('qty');

                $available = $level->on_hand - $activeReserved;

                if ($available < $qty) {
                    throw new InsufficientStockException(
                        "Estoque insuficiente para um dos itens do carrinho (disponível: {$available}, solicitado: {$qty}).",
                    );
                }

                StockReservation::query()->updateOrCreate(
                    [
                        'order_id' => $data->orderId,
                        'product_id' => $productId,
                    ],
                    [
                        'qty' => $qty,
                        'status' => ReservationStatus::Active,
                        'expires_at' => $expiresAt,
                    ],
                );

                $newReserved = $activeReserved + $qty;
                $updates = ['reserved' => $newReserved];
                if (DB::getDriverName() !== 'pgsql') {
                    $updates['available'] = $level->on_hand - $newReserved;
                }

                $level->update($updates);
            }
        });
    }
}
