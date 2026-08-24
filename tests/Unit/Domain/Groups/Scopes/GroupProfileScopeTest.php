<?php

declare(strict_types=1);

use Domain\Groups\Models\Group;
use Domain\Groups\Scopes\GroupProfileScope;
use Domain\Users\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Scout\Builder;

it('filters by the authenticated user when no profile is active', function (): void {
    $user = User::factory()->make(['id' => 42]);
    Auth::login($user);

    $builder = new Builder(new Group, '*');

    (new GroupProfileScope)($builder);

    expect($builder->wheres)->toContain([
        'field' => 'user_id',
        'operator' => '=',
        'value' => '42',
    ]);
});

it('filters by a placeholder value when no user is authenticated', function (): void {
    $builder = new Builder(new Group, '*');

    (new GroupProfileScope)($builder);

    expect($builder->wheres)->toContain([
        'field' => 'user_id',
        'operator' => '=',
        'value' => '__no_user__',
    ]);
});
