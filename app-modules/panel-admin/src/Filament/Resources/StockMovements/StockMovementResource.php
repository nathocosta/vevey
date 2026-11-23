<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\StockMovements;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;
use Vevey\Inventory\Models\StockMovement;
use Vevey\PanelAdmin\Filament\Resources\StockMovements\Pages\ListStockMovements;
use Vevey\PanelAdmin\Filament\Resources\StockMovements\Tables\StockMovementsTable;

final class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;

    protected static ?string $slug = 'stock-movements';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?int $navigationSort = 20;

    #[Override]
    public static function getModelLabel(): string
    {
        return __('panel-admin::inventory.stock_movements.label');
    }

    #[Override]
    public static function getPluralModelLabel(): string
    {
        return __('panel-admin::inventory.stock_movements.plural_label');
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return __('panel-admin::inventory.navigation_group');
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return StockMovementsTable::configure($table);
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListStockMovements::route('/'),
        ];
    }
}
