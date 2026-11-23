<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ADR 7: normalized contact identities per customer with soft retirement (`retired_at`).
     * Partial unique (type, value) WHERE retired_at IS NULL allows reassigning a retired phone number.
     */
    public function up(): void
    {
        Schema::create('customer_identities', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('type');
            $table->string('value')->index();
            $table->string('source')->default('web');
            $table->boolean('is_primary')->default(false);
            $table->timestampTz('verified_at')->nullable();
            $table->timestampTz('retired_at')->nullable()->index();
            $table->timestampsTz();

            $table->index(['type', 'value']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('create unique index customer_identities_active_unique on customer_identities (type, value) where retired_at is null');
            DB::statement('create unique index customer_identities_primary_unique on customer_identities (customer_id, type) where is_primary = true and retired_at is null');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_identities');
    }
};
