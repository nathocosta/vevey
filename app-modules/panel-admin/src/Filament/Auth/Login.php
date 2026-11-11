<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Override;
use Vevey\Permissions\Console\Commands\SyncPermissions\SyncPermissionsCommand;

/**
 * Locally, the form comes filled with the super admin created by DatabaseSeeder.
 */
final class Login extends BaseLogin
{
    #[Override]
    public function mount(): void
    {
        parent::mount();

        if (app()->isLocal()) {
            $this->form->fill([
                'email' => SyncPermissionsCommand::SUPER_ADMIN_EMAIL,
                'password' => 'password',
                'remember' => true,
            ]);
        }
    }
}
