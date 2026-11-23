<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\StockLevels;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;
use Vevey\Inventory\Models\StockLevel;
use Vevey\PanelAdmin\Filament\Resources\StockLevels\Pages\ListStockLevels;
use Vevey\PanelAdmin\Filament\Resources\StockLevels\Tables\StockLevelsTable;

/**
 * ADR 2 & 5: `on_hand` is never directly editable. Staff uses "Lançar entrada" or "Contagem / Ajustar estoque".
 */
final class StockLevelResource extends Resource
{
    protected static ?string $model = StockLevel::class;

    protected static ?string $slug = 'stock-levels';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?int $navigationSort = 10;

    #[Override]
    public static function getModelLabel(): string
    {
        return __('panel-admin::inventory.stock_levels.label');
    }

    #[Override]
    public static function getPluralModelLabel(): string
    {
        return __('panel-admin::inventory.stock_levels.plural_label');
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return __('panel-admin::inventory.navigation_group');
    }

    #[Override]
    public static function getNavigationBadge(): ?string
    {
        $alerts = StockLevel::query()
            ->whereColumn('on_hand', '<=', 'reorder_point')
            ->count();

        return $alerts > 0 ? (string) $alerts : null;
    }

    #[Override]
    public static function getNavigationBadgeColor(): string
    {
        return StockLevel::query()->where('on_hand', '<', 0)->exists()
            ? 'danger'
            : 'warning';
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return StockLevelsTable::configure($table);
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListStockLevels::route('/'),
        ];
    }
}
