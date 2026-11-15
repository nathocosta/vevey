<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

/**
 * Media of spatie/laravel-medialibrary with a UUID key.
 *
 * @property string $id
 * @property string $model_type
 * @property string $model_id
 * @property string|null $uuid
 * @property string $collection_name
 * @property string $name
 * @property string $file_name
 * @property string|null $mime_type
 * @property string $disk
 * @property string|null $conversions_disk
 * @property int $size
 * @property array<string, mixed> $manipulations
 * @property array<string, mixed> $custom_properties
 * @property array<string, bool> $generated_conversions
 * @property array<string, mixed> $responsive_images
 * @property int|null $order_column
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Table(name: 'media')]
final class Media extends BaseMedia
{
    use HasUuids;
}
