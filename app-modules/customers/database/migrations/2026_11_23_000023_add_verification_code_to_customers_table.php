<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', static function (Blueprint $table): void {
            $table->string('verification_code', 6)->nullable()->after('email_verified_at');
            $table->timestampTz('verification_expires_at')->nullable()->after('verification_code');
        });
    }

    public function down(): void
    {
        Schema::table('customers', static function (Blueprint $table): void {
            $table->dropColumn(['verification_code', 'verification_expires_at']);
        });
    }
};
