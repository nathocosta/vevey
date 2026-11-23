<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ADR 1 (money in cents + historical totals CHECK), ADR 4 (split payment/fulfillment status,
     * customer & shipping snapshots, nullable customer_id & seller_id), ADR 5 (channel web/pos/whatsapp).
     */
    public function up(): void
    {
        Schema::create('orders', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('number')->unique();
            $table->string('channel')->index();
            $table->string('payment_status')->index();
            $table->string('fulfillment_status')->index();
            $table->string('delivery_method');

            $table->uuid('customer_id')->nullable()->index();
            $table->foreignUuid('seller_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();

            // Customer snapshot (ADR 4)
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable()->index();

            // Shipping snapshot (ADR 4; null when delivery_method = pickup)
            $table->string('shipping_method')->nullable();
            $table->string('tracking_code')->nullable();
            $table->string('shipping_cep', 12)->nullable();
            $table->string('shipping_street')->nullable();
            $table->string('shipping_number', 30)->nullable();
            $table->string('shipping_complement', 120)->nullable();
            $table->string('shipping_neighborhood', 120)->nullable();
            $table->string('shipping_city', 120)->nullable();
            $table->string('shipping_state', 2)->nullable();

            // Money totals in integer cents (ADR 1)
            $table->unsignedBigInteger('subtotal_cents');
            $table->unsignedBigInteger('discount_cents')->default(0);
            $table->unsignedBigInteger('shipping_cents')->default(0);
            $table->unsignedBigInteger('total_cents');

            $table->text('notes')->nullable();

            $table->timestampTz('paid_at')->nullable();
            $table->timestampTz('shipped_at')->nullable();
            $table->timestampTz('delivered_at')->nullable();
            $table->timestampTz('cancelled_at')->nullable();
            $table->timestampsTz();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('alter table orders add constraint orders_totals_check check (total_cents = subtotal_cents - discount_cents + shipping_cents)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
