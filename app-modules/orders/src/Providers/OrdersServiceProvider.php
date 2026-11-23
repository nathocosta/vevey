<?php

declare(strict_types=1);

namespace Vevey\Orders\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Vevey\Orders\Models\Coupon;
use Vevey\Orders\Models\Order;
use Vevey\Orders\Models\OrderAdjustment;
use Vevey\Orders\Models\OrderItem;
use Vevey\Orders\Models\OrderStatusHistory;
use Vevey\Orders\Models\Payment;

final class OrdersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Relation::morphMap([
            'orders' => Order::class,
            'order_items' => OrderItem::class,
            'order_adjustments' => OrderAdjustment::class,
            'order_status_histories' => OrderStatusHistory::class,
            'coupons' => Coupon::class,
            'payments' => Payment::class,
        ]);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }
}
