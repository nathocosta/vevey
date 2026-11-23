<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_addresses', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('label', 60)->default('Principal');
            $table->string('cep', 12);
            $table->string('street');
            $table->string('number', 30);
            $table->string('complement', 120)->nullable();
            $table->string('neighborhood', 120);
            $table->string('city', 120);
            $table->string('state', 2);
            $table->boolean('is_default')->default(false);
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};
