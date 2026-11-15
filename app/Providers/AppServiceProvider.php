<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Every model registers a morph alias (modules in their own ServiceProvider); sync:permissions builds the permissions from this map.
     */
    public function boot(): void
    {
        Relation::enforceMorphMap([
            'users' => User::class,
            'media' => Media::class,
        ]);
    }
}
