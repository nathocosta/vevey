<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\CheckboxList;

/**
 * Groups the options by module and model, reading Permission::$formatted_name (`group-model-action-name`).
 */
final class PermissionsCheckboxList extends CheckboxList
{
    protected string $view = 'panel-admin::filament.forms.permissions-checkbox-list';
}
