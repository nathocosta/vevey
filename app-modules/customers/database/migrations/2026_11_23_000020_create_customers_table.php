<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ADR 7: separate authenticatable guard `customers`.
     * Email, CPF and password are all nullable so WhatsApp/POS customers exist without fake passwords.
     */
    public function up(): void
    {
        Schema::create('customers', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->nullable()->index();
            $table->string('cpf', 14)->nullable()->index();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->timestampTz('email_verified_at')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('create unique index customers_email_active_unique on customers (lower(email)) where email is not null and deleted_at is null');
            DB::statement('create unique index customers_cpf_active_unique on customers (cpf) where cpf is not null and deleted_at is null');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
