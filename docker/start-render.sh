#!/usr/bin/env sh
set -e

# Render's generateValue creates a random string without Laravel's "base64:" prefix.
# Deterministically normalize any non-standard APP_KEY into a valid 32-byte AES-256 key.
if [ -n "${APP_KEY:-}" ]; then
    APP_KEY="$(php -r '
        $k = getenv("APP_KEY") ?: "";
        if (str_starts_with($k, "base64:")) {
            $raw = base64_decode(substr($k, 7), true);
            if ($raw !== false && strlen($raw) === 32) { echo $k; exit(0); }
        }
        if (strlen($k) === 32) { echo $k; exit(0); }
        echo "base64:" . base64_encode(hash("sha256", $k, true));
    ')"
    export APP_KEY
fi

php artisan config:clear
php artisan view:clear
php artisan storage:link || true

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
