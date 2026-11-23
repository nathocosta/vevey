<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ADR 2 & 5: balance cache per product (one row per product).
     * Intentionally NO CHECK (on_hand >= 0) because POS sales allow negative stock with alert.
     */
    public function up(): void
    {
        Schema::create('stock_levels', static function (Blueprint $table): void {
            $table->foreignUuid('product_id')->primary()->constrained('products')->cascadeOnDelete();
            $table->integer('on_hand')->default(0);
            $table->unsignedInteger('reserved')->default(0);
            if (DB::getDriverName() === 'pgsql') {
                $table->integer('available')->storedAs('on_hand - reserved');
            } else {
                $table->integer('available')->default(0);
            }
            $table->unsignedInteger('reorder_point')->default(5);
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_levels');
    }
};
