#!/usr/bin/env sh
set -e

php artisan config:clear
php artisan view:clear

# Run migrations and idempotent seeds on boot
php artisan migrate --force
php artisan db:seed --class="Vevey\Catalog\Database\Seeders\CatalogSeeder" --force || true
php artisan db:seed --class="Vevey\Inventory\Database\Seeders\InitialStockSeeder" --force || true
php artisan db:seed --class="Database\Seeders\StaffUsersSeeder" --force || true
php artisan sync:permissions || true

# Ensure demo coupon VEVEY10 exists
php -r '
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Vevey\Orders\Models\Coupon::query()->firstOrCreate(
    ["code" => "VEVEY10"],
    ["type" => Vevey\Orders\Enums\CouponType::Percent, "value" => 10, "min_subtotal_cents" => Brick\Money\Money::ofMinor(3000, App\Casts\MoneyCast::CURRENCY), "is_active" => true]
);
' || true

PORT="${PORT:-10000}"
exec php -S "0.0.0.0:${PORT}" -t public
