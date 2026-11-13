<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivot between categories and products.
     * Restrict on the catalog side: a category cannot be deleted while products use it (no silent unlinking).
     */
    public function up(): void
    {
        Schema::create('category_product', static function (Blueprint $table): void {
            $table->foreignUuid('category_id')->constrained('categories')->restrictOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();

            $table->primary(['category_id', 'product_id']);
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_product');
    }
};
