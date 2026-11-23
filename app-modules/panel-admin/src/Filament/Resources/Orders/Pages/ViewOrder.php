<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Resources\Orders\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Override;
use Vevey\Orders\Actions\UpdateOrderStatus;
use Vevey\Orders\Enums\FulfillmentStatus;
use Vevey\Orders\Enums\PaymentStatus;
use Vevey\Orders\Models\Order;
use Vevey\PanelAdmin\Filament\Resources\Orders\OrderResource;

final class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('confirmPayment')
                ->label('Confirmar pagamento')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->visible(fn (): bool => $this->getOrder()->payment_status === PaymentStatus::Pending)
                ->requiresConfirmation()
                ->action(function (UpdateOrderStatus $updater): void {
                    $order = $this->getOrder();
                    $updater->handle(
                        order: $order,
                        paymentStatus: PaymentStatus::Paid,
                        fulfillmentStatus: $order->fulfillment_status === FulfillmentStatus::Pending
                            ? FulfillmentStatus::Preparing
                            : null,
                        userId: Auth::id() ? (string) Auth::id() : null,
                        note: 'Pagamento confirmado pelo painel administrativo.',
                    );

                    Notification::make()
                        ->title('Pagamento confirmado')
                        ->success()
                        ->send();

                    $this->refreshFormData(['payment_status', 'fulfillment_status', 'paid_at']);
                }),

            Action::make('updateFulfillment')
                ->label('Atualizar entrega')
                ->icon(Heroicon::OutlinedTruck)
                ->color('info')
                ->visible(fn (): bool => ! in_array($this->getOrder()->fulfillment_status, [FulfillmentStatus::Delivered, FulfillmentStatus::Cancelled], true))
                ->schema([
                    Select::make('fulfillment_status')
                        ->label('Novo status de entrega')
                        ->options(FulfillmentStatus::class)
                        ->default(fn (): string => $this->getOrder()->fulfillment_status->value)
                        ->required(),
                    Textarea::make('note')
                        ->label('Observação (histórico)')
                        ->rows(2),
                ])
                ->action(function (array $data, UpdateOrderStatus $updater): void {
                    $updater->handle(
                        order: $this->getOrder(),
                        fulfillmentStatus: FulfillmentStatus::from((string) $data['fulfillment_status']),
                        userId: Auth::id() ? (string) Auth::id() : null,
                        note: isset($data['note']) && $data['note'] !== '' ? (string) $data['note'] : null,
                    );

                    Notification::make()
                        ->title('Status de entrega atualizado')
                        ->success()
                        ->send();

                    $this->refreshFormData(['fulfillment_status', 'shipped_at', 'delivered_at', 'cancelled_at']);
                }),
        ];
    }

    private function getOrder(): Order
    {
        /** @var Order $record */
        $record = $this->getRecord();

        return $record;
    }
}
