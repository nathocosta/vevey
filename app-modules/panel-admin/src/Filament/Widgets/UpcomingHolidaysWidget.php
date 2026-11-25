<?php

declare(strict_types=1);

namespace Vevey\PanelAdmin\Filament\Widgets;

use App\Services\BrasilApiService;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Override;

/**
 * Displays upcoming Brazilian national holidays from BrasilAPI so the owner can plan
 * seasonal candy & party-supply stock replenishment.
 */
final class UpcomingHolidaysWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 5;

    protected ?string $heading = 'Próximos feriados nacionais (BrasilAPI — planejamento sazonal)';

    #[Override]
    protected function getStats(): array
    {
        $holidays = app(BrasilApiService::class)->upcomingHolidays(3);

        if ($holidays === []) {
            return [
                Stat::make('Feriados nacionais', 'Indisponível')
                    ->description('Sem conexão com a BrasilAPI no momento')
                    ->color('gray'),
            ];
        }

        return array_map(
            static fn (array $holiday): Stat => Stat::make($holiday['name'], $holiday['formatted_date'])
                ->description($holiday['days_until'] === 0
                    ? 'Hoje!'
                    : ($holiday['days_until'] === 1 ? 'Amanhã' : "Em {$holiday['days_until']} dias"))
                ->descriptionIcon(Heroicon::OutlinedCalendarDays)
                ->color($holiday['days_until'] <= 15 ? 'warning' : 'info'),
            $holidays,
        );
    }
}
