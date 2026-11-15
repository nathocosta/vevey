<?php

declare(strict_types=1);

namespace App\Casts;

use Brick\Money\Money;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * An integer-cents column (`*_cents`, ADR 1) read as a BRL Money and written only from a BRL Money, so a float or a
 * value in reais never reaches the column by accident. Arrays and JSON (including Filament form state) keep the cents.
 *
 * @implements CastsAttributes<Money|null, Money|null>
 */
final class MoneyCast implements CastsAttributes, SerializesCastableAttributes
{
    public const string CURRENCY = 'BRL';

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Money
    {
        if ($value === null) {
            return null;
        }

        return Money::ofMinor((int) $value, self::CURRENCY);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        if ($value === null) {
            return null;
        }

        if ($value->getCurrency()->getCurrencyCode() !== self::CURRENCY) {
            throw new InvalidArgumentException(sprintf('%s::$%s only takes %s.', $model::class, $key, self::CURRENCY));
        }

        return $value->getMinorAmount()->toInt();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function serialize(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        return $value instanceof Money ? $value->getMinorAmount()->toInt() : null;
    }
}
