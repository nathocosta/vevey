<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Override;
use Vevey\Orders\Enums\OrderChannel;
use Vevey\Orders\Enums\PaymentStatus;
use Vevey\Orders\Models\Order;

final class SalesByChannelChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Vendas pagas por canal (R$)';

    protected ?string $maxHeight = '260px';

    #[Override]
    protected function getType(): string
    {
        return 'doughnut';
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    protected function getData(): array
    {
        $totalsByChannel = Order::query()
            ->where('payment_status', PaymentStatus::Paid)
            ->selectRaw('channel, sum(total_cents) as total')
            ->groupBy('channel')
            ->pluck('total', 'channel');

        $labels = [];
        $values = [];

        foreach (OrderChannel::cases() as $channel) {
            $labels[] = $channel->getLabel();
            $values[] = round(((int) ($totalsByChannel->get($channel->value) ?? 0)) / 100, 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Receita (R$)',
                    'data' => $values,
                    'backgroundColor' => ['#E11D48', '#F59E0B', '#16A34A'],
                ],
            ],
            'labels' => $labels,
        ];
    }
}
