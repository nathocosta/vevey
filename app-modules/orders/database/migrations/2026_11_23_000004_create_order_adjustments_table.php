<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ADR 1: explains orders.discount_cents (coupon, pix_discount, free_shipping, manual).
     */
    public function up(): void
    {
        Schema::create('order_adjustments', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('type');
            $table->string('label');
            $table->unsignedBigInteger('amount_cents');
            $table->json('metadata')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_adjustments');
    }
};
