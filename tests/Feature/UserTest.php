<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Str;

test('user receives a uuid v7 primary key on create', function (): void {
    $user = User::factory()->create();

    expect(Str::isUuid($user->id, 7))->toBeTrue()
        ->and(User::query()->find($user->id)?->is($user))->toBeTrue();
});
