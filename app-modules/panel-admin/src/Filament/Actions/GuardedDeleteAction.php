<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Actions;

use Closure;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

/**
 * Delete that refuses, with the reason, when the record is still in use. The action stays visible on purpose:
 * hiding it would leave the operator guessing why a record cannot be deleted.
 */
final class GuardedDeleteAction
{
    /**
     * @template TModel of Model
     *
     * @param  class-string<TModel>  $model
     * @param  Closure(TModel): ?string  $blockedReason  the explanation shown when deleting is not allowed, null when it is
     */
    public static function make(string $model, Closure $blockedReason): DeleteAction
    {
        return DeleteAction::make()
            ->before(function (DeleteAction $action, Model $record) use ($model, $blockedReason): void {
                $reason = $record instanceof $model ? $blockedReason($record) : null;

                if ($reason === null) {
                    return;
                }

                Notification::make()
                    ->danger()
                    ->title(__('panel-admin::actions.deletion_blocked'))
                    ->body($reason)
                    ->persistent()
                    ->send();

                $action->cancel();
            });
    }
}
