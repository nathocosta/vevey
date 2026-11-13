<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Values of an attribute, such as Azul or Rosa.
     */
    public function up(): void
    {
        Schema::create('attribute_values', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('attribute_id')->constrained('attributes')->cascadeOnDelete();
            $table->string('value');
            $table->string('slug');
            $table->timestampsTz();

            $table->unique(['attribute_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_values');
    }
};
