<?php

declare(strict_types=1);

namespace Vevey\Inventory\Actions;

use Illuminate\Support\Facades\DB;
use Vevey\Inventory\DTOs\RecordSaleData;
use Vevey\Inventory\Enums\ReservationStatus;
use Vevey\Inventory\Enums\StockMovementReason;
use Vevey\Inventory\Events\StockBelowReorderPoint;
use Vevey\Inventory\Exceptions\InsufficientStockException;
use Vevey\Inventory\Models\StockLevel;
use Vevey\Inventory\Models\StockMovement;
use Vevey\Inventory\Models\StockReservation;

/**
 * Direct ledger deduction for POS counter sales (ADR 5).
 * When allowNegative is true (POS), stock is allowed to go negative and triggers reorder alert.
 */
final class RecordDirectSale
{
    /**
     * @return list<string> product IDs that ended up with negative on_hand after the sale
     */
    public function handle(RecordSaleData $data): array
    {
        if ($data->items === []) {
            return [];
        }

        $qtyByProduct = [];
        foreach ($data->items as $item) {
            $qtyByProduct[$item->productId] = ($qtyByProduct[$item->productId] ?? 0) + $item->quantity;
        }

        ksort($qtyByProduct);
        $productIds = array_keys($qtyByProduct);

        return DB::transaction(static function () use ($data, $qtyByProduct, $productIds): array {
            $now = now();
            $negativeProductIds = [];

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
                $alreadyRecorded = StockMovement::query()
                    ->where('reference_type', 'orders')
                    ->where('reference_id', $data->orderId)
                    ->where('reason', StockMovementReason::Sale)
                    ->where('product_id', $productId)
                    ->exists();

                if ($alreadyRecorded) {
                    continue;
                }

                /** @var StockLevel $level */
                $level = $levels->get($productId);

                $activeReserved = (int) StockReservation::query()
                    ->where('product_id', $productId)
                    ->where('status', ReservationStatus::Active)
                    ->where('expires_at', '>', $now)
                    ->sum('qty');

                if (! $data->allowNegative && ($level->on_hand - $activeReserved) < $qty) {
                    throw new InsufficientStockException('Estoque insuficiente para concluir a venda.');
                }

                $newOnHand = $level->on_hand - $qty;

                $movementData = [
                    'product_id' => $productId,
                    'qty' => -$qty,
                    'reason' => StockMovementReason::Sale,
                    'reference_type' => 'orders',
                    'reference_id' => $data->orderId,
                    'balance_after' => $newOnHand,
                    'user_id' => $data->userId,
                    'note' => $data->note ?? 'Venda no balcão (PDV).',
                    'created_at' => $now,
                ];

                if (DB::getDriverName() !== 'pgsql') {
                    $movementData['direction'] = 'out';
                }

                StockMovement::query()->create($movementData);

                $updates = [
                    'on_hand' => $newOnHand,
                    'reserved' => $activeReserved,
                ];
                if (DB::getDriverName() !== 'pgsql') {
                    $updates['available'] = $newOnHand - $activeReserved;
                }

                $level->update($updates);

                if ($newOnHand < 0) {
                    $negativeProductIds[] = $productId;
                }

                if ($newOnHand <= $level->reorder_point) {
                    StockBelowReorderPoint::dispatch(
                        productId: $productId,
                        onHand: $newOnHand,
                        reorderPoint: $level->reorder_point,
                    );
                }
            }

            return $negativeProductIds;
        });
    }
}
