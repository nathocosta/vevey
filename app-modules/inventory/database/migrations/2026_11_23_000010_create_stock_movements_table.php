<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ADR 2: append-only single-entry stock ledger.
     * qty is signed (+in, -out); direction is a stored generated column on Postgres.
     * Unique (reference_type, reference_id, reason, product_id) prevents duplicate deductions.
     */
    public function up(): void
    {
        Schema::create('stock_movements', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->restrictOnDelete();
            $table->integer('qty');
            if (DB::getDriverName() === 'pgsql') {
                $table->string('direction')->storedAs("case when qty > 0 then 'in' else 'out' end");
            } else {
                $table->string('direction')->nullable();
            }
            $table->string('reason')->index();
            $table->nullableUuidMorphs('reference');
            $table->integer('balance_after');
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestampTz('created_at');

            $table->unique(
                ['reference_type', 'reference_id', 'reason', 'product_id'],
                'stock_movements_idempotency_unique',
            );
            $table->index(['product_id', 'created_at']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('alter table stock_movements add constraint stock_movements_qty_nonzero check (qty <> 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
