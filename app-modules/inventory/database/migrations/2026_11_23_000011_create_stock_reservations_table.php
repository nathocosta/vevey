<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ADR 2: reservations created at checkout before payment confirmation (lazy expiration).
     */
    public function up(): void
    {
        Schema::create('stock_reservations', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->restrictOnDelete();
            $table->unsignedInteger('qty');
            $table->string('status')->index();
            $table->timestampTz('expires_at')->index();
            $table->timestampsTz();

            $table->unique(['order_id', 'product_id']);
            $table->index(['product_id', 'status', 'expires_at']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('alter table stock_reservations add constraint stock_reservations_qty_positive check (qty > 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_reservations');
    }
};
