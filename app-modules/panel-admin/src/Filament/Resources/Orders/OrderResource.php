<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Orders;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;
use Vevey\Orders\Models\Order;
use Vevey\PanelAdmin\Filament\Resources\Orders\Pages\ListOrders;
use Vevey\PanelAdmin\Filament\Resources\Orders\Pages\ViewOrder;
use Vevey\PanelAdmin\Filament\Resources\Orders\Schemas\OrderInfolist;
use Vevey\PanelAdmin\Filament\Resources\Orders\Tables\OrdersTable;

final class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $slug = 'orders';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'number';

    #[Override]
    public static function getModelLabel(): string
    {
        return __('panel-admin::orders.orders.label');
    }

    #[Override]
    public static function getPluralModelLabel(): string
    {
        return __('panel-admin::orders.orders.plural_label');
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return __('panel-admin::orders.navigation_group');
    }

    #[Override]
    public static function infolist(Schema $schema): Schema
    {
        return OrderInfolist::configure($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return OrdersTable::configure($table);
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'view' => ViewOrder::route('/{record}'),
        ];
    }

    /**
     * @return list<string>
     */
    #[Override]
    public static function getGloballySearchableAttributes(): array
    {
        return ['number', 'customer_name', 'customer_phone', 'customer_email'];
    }
}
