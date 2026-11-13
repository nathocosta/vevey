<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Flat catalog: every color is its own product (ADR 6). Prices in integer cents (ADR 1);
     * soft deleted because order lines reference them. The CHECK keeps prices positive and the promotional price
     * below the regular one even outside the panel (Postgres only: SQLite cannot add a constraint to an existing table).
     */
    public function up(): void
    {
        Schema::create('products', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('price_cents');
            $table->unsignedBigInteger('promo_price_cents')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('alter table products add constraint products_prices_check check (price_cents > 0 and (promo_price_cents is null or (promo_price_cents > 0 and promo_price_cents < price_cents)))');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
