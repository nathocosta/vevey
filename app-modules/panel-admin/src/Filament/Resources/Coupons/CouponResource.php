<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Coupons;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;
use Vevey\Orders\Models\Coupon;
use Vevey\PanelAdmin\Filament\Resources\Coupons\Pages\CreateCoupon;
use Vevey\PanelAdmin\Filament\Resources\Coupons\Pages\EditCoupon;
use Vevey\PanelAdmin\Filament\Resources\Coupons\Pages\ListCoupons;
use Vevey\PanelAdmin\Filament\Resources\Coupons\Schemas\CouponForm;
use Vevey\PanelAdmin\Filament\Resources\Coupons\Tables\CouponsTable;

final class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?string $slug = 'coupons';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static ?int $navigationSort = 20;

    protected static ?string $recordTitleAttribute = 'code';

    #[Override]
    public static function getModelLabel(): string
    {
        return __('panel-admin::orders.coupons.label');
    }

    #[Override]
    public static function getPluralModelLabel(): string
    {
        return __('panel-admin::orders.coupons.plural_label');
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return __('panel-admin::orders.navigation_group');
    }

    #[Override]
    public static function form(Schema $schema): Schema
    {
        return CouponForm::configure($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return CouponsTable::configure($table);
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListCoupons::route('/'),
            'create' => CreateCoupon::route('/create'),
            'edit' => EditCoupon::route('/{record}/edit'),
        ];
    }
}
