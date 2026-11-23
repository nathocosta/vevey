<?php

declare(strict_types=1);

namespace Vevey\Orders\Models;

use App\Casts\MoneyCast;
use App\Models\User;
use Brick\Money\Money;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Vevey\Orders\Enums\DeliveryMethod;
use Vevey\Orders\Enums\FulfillmentStatus;
use Vevey\Orders\Enums\OrderChannel;
use Vevey\Orders\Enums\PaymentStatus;
use Vevey\Orders\Policies\OrderPolicy;

/**
 * Historical order record (ADR 1, 3, 4, 5). Totals and snapshots are written at creation time
 * and never recalculated from the current catalog.
 *
 * @property string $id
 * @property string $number
 * @property OrderChannel $channel
 * @property PaymentStatus $payment_status
 * @property FulfillmentStatus $fulfillment_status
 * @property DeliveryMethod $delivery_method
 * @property string|null $customer_id
 * @property string|null $seller_id
 * @property string|null $coupon_id
 * @property string $customer_name
 * @property string|null $customer_email
 * @property string|null $customer_phone
 * @property string|null $shipping_method
 * @property string|null $tracking_code
 * @property string|null $shipping_cep
 * @property string|null $shipping_street
 * @property string|null $shipping_number
 * @property string|null $shipping_complement
 * @property string|null $shipping_neighborhood
 * @property string|null $shipping_city
 * @property string|null $shipping_state
 * @property Money $subtotal_cents
 * @property Money $discount_cents
 * @property Money $shipping_cents
 * @property Money $total_cents
 * @property string|null $notes
 * @property Carbon|null $paid_at
 * @property Carbon|null $shipped_at
 * @property Carbon|null $delivered_at
 * @property Carbon|null $cancelled_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $seller
 * @property-read Coupon|null $coupon
 * @property-read Collection<int, OrderItem> $items
 * @property-read Collection<int, OrderAdjustment> $adjustments
 * @property-read Collection<int, OrderStatusHistory> $statusHistories
 * @property-read Collection<int, Payment> $payments
 */
#[UsePolicy(OrderPolicy::class)]
#[Fillable([
    'number',
    'channel',
    'payment_status',
    'fulfillment_status',
    'delivery_method',
    'customer_id',
    'seller_id',
    'coupon_id',
    'customer_name',
    'customer_email',
    'customer_phone',
    'shipping_method',
    'tracking_code',
    'shipping_cep',
    'shipping_street',
    'shipping_number',
    'shipping_complement',
    'shipping_neighborhood',
    'shipping_city',
    'shipping_state',
    'subtotal_cents',
    'discount_cents',
    'shipping_cents',
    'total_cents',
    'notes',
    'paid_at',
    'shipped_at',
    'delivered_at',
    'cancelled_at',
])]
#[Table(name: 'orders')]
final class Order extends Model
{
    use HasUuids;

    /**
     * @return BelongsTo<User, $this>
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * @return BelongsTo<Coupon, $this>
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasMany<OrderAdjustment, $this>
     */
    public function adjustments(): HasMany
    {
        return $this->hasMany(OrderAdjustment::class);
    }

    /**
     * @return HasMany<OrderStatusHistory, $this>
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->latest('created_at');
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'channel' => OrderChannel::class,
            'payment_status' => PaymentStatus::class,
            'fulfillment_status' => FulfillmentStatus::class,
            'delivery_method' => DeliveryMethod::class,
            'subtotal_cents' => MoneyCast::class,
            'discount_cents' => MoneyCast::class,
            'shipping_cents' => MoneyCast::class,
            'total_cents' => MoneyCast::class,
            'paid_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }
}
