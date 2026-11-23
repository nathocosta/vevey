<?php

declare(strict_types=1);

namespace Vevey\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Vevey\Inventory\Actions\ReceiveStock;
use Vevey\Inventory\DTOs\ReceiveStockData;
use Vevey\Inventory\Models\StockLevel;

/**
 * Seeds an initial `purchase` entry of 50 units for every product without a stock_levels row yet.
 * Uses DB::table('products') to avoid importing Vevey\Catalog models (ADR 8).
 */
final class InitialStockSeeder extends Seeder
{
    public function run(ReceiveStock $receiveStock): void
    {
        $existingIds = StockLevel::query()->pluck('product_id')->all();

        $productIds = DB::table('products')
            ->whereNull('deleted_at')
            ->whereNotIn('id', $existingIds)
            ->pluck('id');

        foreach ($productIds as $productId) {
            $receiveStock->handle(new ReceiveStockData(
                productId: (string) $productId,
                quantity: 50,
                note: 'Estoque inicial de implantação Vevey.',
                reorderPoint: 10,
            ));
        }
    }
}
