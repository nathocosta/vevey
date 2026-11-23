<?php

declare(strict_types=1);

namespace Vevey\Orders\Actions;

use Illuminate\Support\Facades\DB;
use Vevey\Inventory\Actions\FulfillOrderReservations;
use Vevey\Inventory\Actions\ReleaseOrderReservations;
use Vevey\Orders\Enums\FulfillmentStatus;
use Vevey\Orders\Enums\PaymentStatus;
use Vevey\Orders\Models\Order;
use Vevey\Orders\Models\OrderStatusHistory;
use Vevey\Orders\Models\Payment;

/**
 * Transitions order payment and/or fulfillment status, updates transition timestamps
 * and records an entry in order_status_histories (ADR 4 & RNF011).
 * Coordinates stock fulfillment on payment confirmation and release on cancellation (ADR 2).
 */
final class UpdateOrderStatus
{
    public function __construct(
        private readonly FulfillOrderReservations $fulfillOrderReservations,
        private readonly ReleaseOrderReservations $releaseOrderReservations,
    ) {}
    public function handle(
        Order $order,
        ?PaymentStatus $paymentStatus = null,
        ?FulfillmentStatus $fulfillmentStatus = null,
        ?string $userId = null,
        ?string $note = null,
    ): Order {
        return DB::transaction(function () use ($order, $paymentStatus, $fulfillmentStatus, $userId, $note): Order {
            $now = now();
            $updates = [];

            if ($paymentStatus instanceof PaymentStatus && $order->payment_status !== $paymentStatus) {
                $from = $order->payment_status->value;
                $updates['payment_status'] = $paymentStatus;

                if ($paymentStatus === PaymentStatus::Paid && $order->paid_at === null) {
                    $updates['paid_at'] = $now;
                }

                OrderStatusHistory::query()->create([
                    'order_id' => $order->id,
                    'field' => 'payment_status',
                    'from_status' => $from,
                    'to_status' => $paymentStatus->value,
                    'user_id' => $userId,
                    'note' => $note,
                    'created_at' => $now,
                ]);

                $latestPayment = $order->payments()->first();
                if ($latestPayment instanceof Payment) {
                    $latestPayment->update([
                        'status' => $paymentStatus,
                        'paid_at' => $paymentStatus === PaymentStatus::Paid ? ($latestPayment->paid_at ?? $now) : $latestPayment->paid_at,
                    ]);
                }

                if ($paymentStatus === PaymentStatus::Paid) {
                    $this->fulfillOrderReservations->handle(
                        orderId: $order->id,
                        userId: $userId,
                        note: $note ?? "Pagamento confirmado ({$order->number}).",
                    );
                }

                if (in_array($paymentStatus, [PaymentStatus::Expired, PaymentStatus::Failed], true)) {
                    $this->releaseOrderReservations->handle($order->id);
                }
            }

            if ($fulfillmentStatus instanceof FulfillmentStatus && $order->fulfillment_status !== $fulfillmentStatus) {
                $from = $order->fulfillment_status->value;
                $updates['fulfillment_status'] = $fulfillmentStatus;

                if ($fulfillmentStatus === FulfillmentStatus::Shipped && $order->shipped_at === null) {
                    $updates['shipped_at'] = $now;
                }

                if ($fulfillmentStatus === FulfillmentStatus::Delivered && $order->delivered_at === null) {
                    $updates['delivered_at'] = $now;
                }

                if ($fulfillmentStatus === FulfillmentStatus::Cancelled && $order->cancelled_at === null) {
                    $updates['cancelled_at'] = $now;
                    $this->releaseOrderReservations->handle($order->id);
                }

                OrderStatusHistory::query()->create([
                    'order_id' => $order->id,
                    'field' => 'fulfillment_status',
                    'from_status' => $from,
                    'to_status' => $fulfillmentStatus->value,
                    'user_id' => $userId,
                    'note' => $note,
                    'created_at' => $now,
                ]);
            }

            if ($updates !== []) {
                $order->update($updates);
            }

            return $order->refresh();
        });
    }
}
