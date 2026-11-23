<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Customers;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;
use Vevey\Customers\Models\Customer;
use Vevey\PanelAdmin\Filament\Resources\Customers\Pages\ListCustomers;
use Vevey\PanelAdmin\Filament\Resources\Customers\Pages\ViewCustomer;
use Vevey\PanelAdmin\Filament\Resources\Customers\Schemas\CustomerInfolist;
use Vevey\PanelAdmin\Filament\Resources\Customers\Tables\CustomersTable;

final class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $slug = 'customers';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'name';

    #[Override]
    public static function getModelLabel(): string
    {
        return __('panel-admin::customers.customers.label');
    }

    #[Override]
    public static function getPluralModelLabel(): string
    {
        return __('panel-admin::customers.customers.plural_label');
    }

    #[Override]
    public static function getNavigationGroup(): string
    {
        return __('panel-admin::customers.navigation_group');
    }

    #[Override]
    public static function infolist(Schema $schema): Schema
    {
        return CustomerInfolist::configure($schema);
    }

    #[Override]
    public static function table(Table $table): Table
    {
        return CustomersTable::configure($table);
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListCustomers::route('/'),
            'view' => ViewCustomer::route('/{record}'),
        ];
    }
}
