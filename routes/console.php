<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ADR 2: libera periodicamente reservas de estoque vencidas (a correção não depende do job, pois a leitura é lazy)
Schedule::command('stock:expire-reservations')->everyFiveMinutes();
