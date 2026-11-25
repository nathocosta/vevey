<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Integrates safe, public, HTTPS Brazilian APIs (BrasilAPI + ViaCEP fallback) with caching and graceful fallbacks,
 * plus official Mod-11 CPF check-digit validation (LGPD-compliant; no personal data queried externally).
 */
final class BrasilApiService
{
    /**
     * @return array{street: string, neighborhood: string, city: string, state: string, provider: string}|null
     */
    public function lookupCep(string $rawCep): ?array
    {
        $cep = preg_replace('/\D+/', '', $rawCep) ?? '';
        if (strlen($cep) !== 8) {
            return null;
        }

        $cacheKey = "vevey:cep:{$cep}";
        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        // 1. Primary: BrasilAPI CEP v2
        try {
            $response = Http::timeout(4)
                ->acceptJson()
                ->get("https://brasilapi.com.br/api/cep/v2/{$cep}");

            if ($response->successful()) {
                /** @var array<string, mixed> $data */
                $data = $response->json() ?? [];
                if (! empty($data['city']) || ! empty($data['street'])) {
                    $resolved = [
                        'street' => (string) ($data['street'] ?? ''),
                        'neighborhood' => (string) ($data['neighborhood'] ?? ''),
                        'city' => (string) ($data['city'] ?? ''),
                        'state' => (string) ($data['state'] ?? ''),
                        'provider' => 'brasilapi',
                    ];
                    Cache::put($cacheKey, $resolved, now()->addHours(24));

                    return $resolved;
                }
            }
        } catch (Throwable) {
            // Fall through to ViaCEP
        }

        // 2. Fallback: ViaCEP
        try {
            $response = Http::timeout(4)
                ->acceptJson()
                ->get("https://viacep.com.br/ws/{$cep}/json/");

            if ($response->successful()) {
                /** @var array<string, mixed> $data */
                $data = $response->json() ?? [];
                if (($data['erro'] ?? false) !== true) {
                    $resolved = [
                        'street' => (string) ($data['logradouro'] ?? ''),
                        'neighborhood' => (string) ($data['bairro'] ?? ''),
                        'city' => (string) ($data['localidade'] ?? ''),
                        'state' => (string) ($data['uf'] ?? ''),
                        'provider' => 'viacep',
                    ];
                    Cache::put($cacheKey, $resolved, now()->addHours(24));

                    return $resolved;
                }
            }
        } catch (Throwable) {
            // Both providers unavailable
        }

        return null;
    }

    /**
     * @return list<array{date: string, name: string, type: string}>
     */
    public function nationalHolidays(int $year): array
    {
        return Cache::remember("vevey:holidays:{$year}", now()->addDays(7), static function () use ($year): array {
            try {
                $response = Http::timeout(4)
                    ->acceptJson()
                    ->get("https://brasilapi.com.br/api/feriados/v1/{$year}");

                if (! $response->successful()) {
                    return [];
                }

                /** @var list<array{date?: string, name?: string, type?: string}> $items */
                $items = $response->json() ?? [];
                $result = [];

                foreach ($items as $item) {
                    if (! empty($item['date']) && ! empty($item['name'])) {
                        $result[] = [
                            'date' => (string) $item['date'],
                            'name' => (string) $item['name'],
                            'type' => (string) ($item['type'] ?? 'national'),
                        ];
                    }
                }

                return $result;
            } catch (Throwable) {
                return [];
            }
        });
    }

    /**
     * Adds real Brazilian business days (skipping weekends and BrasilAPI national holidays).
     */
    public function addBusinessDays(CarbonImmutable $start, int $businessDays): CarbonImmutable
    {
        $holidayDates = [];
        foreach ([$start->year, $start->year + 1] as $year) {
            foreach ($this->nationalHolidays($year) as $holiday) {
                $holidayDates[$holiday['date']] = true;
            }
        }

        $cursor = $start;
        $added = 0;

        while ($added < $businessDays) {
            $cursor = $cursor->addDay();
            if ($cursor->isWeekend()) {
                continue;
            }
            if (isset($holidayDates[$cursor->format('Y-m-d')])) {
                continue;
            }
            $added++;
        }

        return $cursor;
    }

    /**
     * @return list<array{date: string, formatted_date: string, name: string, days_until: int}>
     */
    public function upcomingHolidays(int $limit = 4): array
    {
        $today = CarbonImmutable::today();
        $all = array_merge(
            $this->nationalHolidays($today->year),
            $this->nationalHolidays($today->year + 1),
        );

        $upcoming = [];
        foreach ($all as $holiday) {
            $date = CarbonImmutable::parse($holiday['date']);
            if ($date->lt($today)) {
                continue;
            }

            $upcoming[] = [
                'date' => $holiday['date'],
                'formatted_date' => $date->format('d/m/Y'),
                'name' => $holiday['name'],
                'days_until' => (int) $today->diffInDays($date),
            ];

            if (count($upcoming) >= $limit) {
                break;
            }
        }

        return $upcoming;
    }

    /**
     * Official Brazilian CPF Mod-11 check-digit validation (rejects repeated digits and invalid checksums).
     */
    public static function isValidCpf(string $rawCpf): bool
    {
        $cpf = preg_replace('/\D+/', '', $rawCpf) ?? '';
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf) === 1) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $sum = 0;
            for ($c = 0; $c < $t; $c++) {
                $sum += ((int) $cpf[$c]) * (($t + 1) - $c);
            }
            $digit = ((10 * $sum) % 11) % 10;
            if ((int) $cpf[$t] !== $digit) {
                return false;
            }
        }

        return true;
    }

    public static function formatCpf(string $rawCpf): string
    {
        $digits = preg_replace('/\D+/', '', $rawCpf) ?? '';
        if (strlen($digits) !== 11) {
            return $digits;
        }

        return substr($digits, 0, 3) . '.' . substr($digits, 3, 3) . '.' . substr($digits, 6, 3) . '-' . substr($digits, 9, 2);
    }
}
