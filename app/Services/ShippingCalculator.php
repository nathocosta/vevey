<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use Throwable;

final class ShippingCalculator
{
    public const int FREE_SHIPPING_THRESHOLD_CENTS = 15000;

    public function __construct(
        private readonly BrasilApiService $brasilApi,
    ) {}

    /**
     * @return array{
     *     cep: string,
     *     address: array{street: string, neighborhood: string, city: string, state: string}|null,
     *     source: 'melhor_envio'|'estimativa',
     *     options: list<array{
     *         code: string,
     *         type: 'pickup'|'delivery',
     *         name: string,
     *         carrier: string,
     *         price_cents: int,
     *         delivery_label: string,
     *         free_shipping_applied: bool
     *     }>
     * }
     */
    public function quote(string $rawCep, int $subtotalCents = 0, float $weightKg = 1.0): array
    {
        $cep = preg_replace('/\D+/', '', $rawCep) ?? '';
        $lookedUp = $this->brasilApi->lookupCep($cep);
        $address = $lookedUp !== null ? [
            'street' => $lookedUp['street'],
            'neighborhood' => $lookedUp['neighborhood'],
            'city' => $lookedUp['city'],
            'state' => $lookedUp['state'],
        ] : null;

        $pickup = [
            'code' => 'pickup',
            'type' => 'pickup',
            'name' => 'Retirada na loja Vevey',
            'carrier' => 'Loja Física',
            'price_cents' => 0,
            'delivery_label' => 'Disponível no mesmo dia',
            'free_shipping_applied' => false,
        ];

        $melhorEnvioOptions = $this->quoteMelhorEnvio($cep, $subtotalCents, $weightKg);

        if ($melhorEnvioOptions !== []) {
            return [
                'cep' => $cep,
                'address' => $address,
                'source' => 'melhor_envio',
                'options' => [$pickup, ...$melhorEnvioOptions],
            ];
        }

        return [
            'cep' => $cep,
            'address' => $address,
            'source' => 'estimativa',
            'options' => [$pickup, ...$this->fallbackQuotes($cep, $subtotalCents)],
        ];
    }

    /**
     * @return list<array{
     *     code: string,
     *     type: 'delivery',
     *     name: string,
     *     carrier: string,
     *     price_cents: int,
     *     delivery_label: string,
     *     free_shipping_applied: bool
     * }>
     */
    private function quoteMelhorEnvio(string $cep, int $subtotalCents, float $weightKg): array
    {
        $token = (string) config('services.melhor_envio.token', '');

        if ($token === '' || strlen($cep) !== 8) {
            return [];
        }

        $baseUrl = rtrim((string) config('services.melhor_envio.base_url', 'https://sandbox.melhorenvio.com.br'), '/');
        $originCep = preg_replace('/\D+/', '', (string) config('services.melhor_envio.origin_cep', '01310100')) ?? '01310100';

        try {
            $response = Http::timeout(6)
                ->withToken($token)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'User-Agent' => (string) config('services.melhor_envio.user_agent', 'VeveyDoces (contato@veveydoces.com.br)'),
                ])
                ->post("{$baseUrl}/api/v2/me/shipment/calculate", [
                    'from' => ['postal_code' => $originCep],
                    'to' => ['postal_code' => $cep],
                    'package' => [
                        'height' => 16,
                        'width' => 24,
                        'length' => 30,
                        'weight' => max(0.3, $weightKg),
                    ],
                    'options' => [
                        'insurance_value' => max(10, round($subtotalCents / 100, 2)),
                        'receipt' => false,
                        'own_hand' => false,
                    ],
                    'services' => '1,2',
                ]);

            if (! $response->successful()) {
                return [];
            }

            /** @var list<array<string, mixed>> $items */
            $items = $response->json() ?? [];
            $options = [];
            $freeEligible = $subtotalCents >= self::FREE_SHIPPING_THRESHOLD_CENTS;

            foreach ($items as $item) {
                if (isset($item['error']) || ! isset($item['price'])) {
                    continue;
                }

                $name = (string) ($item['name'] ?? 'Entrega');
                $priceCents = (int) round(((float) $item['price']) * 100);
                $days = (int) ($item['delivery_time'] ?? 3);
                $isPac = str_contains(mb_strtolower($name), 'pac');
                $applyFree = $freeEligible && $isPac;

                $options[] = [
                    'code' => 'me_' . (string) ($item['id'] ?? mb_strtolower($name)),
                    'type' => 'delivery',
                    'name' => 'Correios ' . $name,
                    'carrier' => (string) ($item['company']['name'] ?? 'Correios'),
                    'price_cents' => $applyFree ? 0 : $priceCents,
                    'delivery_label' => $days === 1 ? 'Até 1 dia útil' : "Até {$days} dias úteis",
                    'free_shipping_applied' => $applyFree,
                ];
            }

            return $options;
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * @return list<array{
     *     code: string,
     *     type: 'delivery',
     *     name: string,
     *     carrier: string,
     *     price_cents: int,
     *     delivery_label: string,
     *     free_shipping_applied: bool
     * }>
     */
    private function fallbackQuotes(string $cep, int $subtotalCents): array
    {
        $prefix = (int) substr($cep, 0, 1);
        $freeEligible = $subtotalCents >= self::FREE_SHIPPING_THRESHOLD_CENTS;

        [$pacCents, $pacDays, $sedexCents, $sedexDays] = match (true) {
            $prefix <= 1 => [1490, 3, 2190, 1],
            $prefix <= 3 => [1890, 5, 2990, 2],
            $prefix <= 5 => [2390, 6, 3890, 3],
            default => [2690, 7, 4490, 3],
        };

        $sedexEta = $this->brasilApi->addBusinessDays(CarbonImmutable::today(), $sedexDays)->format('d/m');
        $pacEta = $this->brasilApi->addBusinessDays(CarbonImmutable::today(), $pacDays)->format('d/m');

        return [
            [
                'code' => 'correios_sedex',
                'type' => 'delivery',
                'name' => 'Correios SEDEX',
                'carrier' => 'Correios',
                'price_cents' => $sedexCents,
                'delivery_label' => ($sedexDays === 1 ? '1 dia útil' : "{$sedexDays} dias úteis") . " (prev. {$sedexEta})",
                'free_shipping_applied' => false,
            ],
            [
                'code' => 'correios_pac',
                'type' => 'delivery',
                'name' => 'Correios PAC',
                'carrier' => 'Correios',
                'price_cents' => $freeEligible ? 0 : $pacCents,
                'delivery_label' => "{$pacDays} dias úteis (prev. {$pacEta})",
                'free_shipping_applied' => $freeEligible,
            ],
        ];
    }
}
