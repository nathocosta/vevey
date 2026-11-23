<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ADR 1: 1:N payments per order (supports retrying after an expired Pix).
     */
    public function up(): void
    {
        Schema::create('payments', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('method');
            $table->string('provider')->default('manual');
            $table->string('provider_id')->nullable()->index();
            $table->unsignedBigInteger('amount_cents');
            $table->string('status')->index();
            $table->timestampTz('expires_at')->nullable();
            $table->timestampTz('paid_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
