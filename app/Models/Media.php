<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

/**
 * Media of spatie/laravel-medialibrary with a UUID key; the other columns are documented on the parent class.
 *
 * @property string $id
 * @property string $model_id
 */
#[Table(name: 'media')]
final class Media extends BaseMedia
{
    use HasUuids;
}
