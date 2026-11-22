<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivot between attribute values and products (filter rule in ADR 6).
     * Restrict on the catalog side: an attribute value cannot be deleted while products use it (no silent unlinking).
     */
    public function up(): void
    {
        Schema::create('attribute_value_product', static function (Blueprint $table): void {
            $table->foreignUuid('attribute_value_id')->constrained('attribute_values')->restrictOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();

            $table->primary(['attribute_value_id', 'product_id']);
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_value_product');
    }
};
