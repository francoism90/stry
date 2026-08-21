<?php

declare(strict_types=1);

use Domain\Users\Filters\UserScopeFilter;
use Domain\Users\Models\User;
use Laravel\Scout\Builder;

it('adds an email_verified_at filter for the verified scope', function (): void {
    $builder = new Builder(new User, '*');

    (new UserScopeFilter)($builder, 'verified', 'scope');

    expect($builder->wheres)->toContain([
        'field' => 'email_verified_at',
        'operator' => '>',
        'value' => 0,
    ]);
});

it('adds an email_verified_at filter for the unverified scope', function (): void {
    $builder = new Builder(new User, '*');

    (new UserScopeFilter)($builder, 'unverified', 'scope');

    expect($builder->wheres)->toContain([
        'field' => 'email_verified_at',
        'operator' => '=',
        'value' => 0,
    ]);
});

it('adds a trashed filter for the deleted scope', function (): void {
    $builder = new Builder(new User, '*');

    (new UserScopeFilter)($builder, 'deleted', 'scope');

    expect($builder->wheres)->toContain([
        'field' => '__soft_deleted',
        'operator' => '=',
        'value' => 1,
    ]);
});

it('does not add a filter for the all scope', function (): void {
    $builder = new Builder(new User, '*');

    (new UserScopeFilter)($builder, 'all', 'scope');

    expect($builder->wheres)->toBe([]);
});

it('ignores unknown scope values', function (): void {
    $builder = new Builder(new User, '*');

    (new UserScopeFilter)($builder, 'bogus', 'scope');

    expect($builder->wheres)->toBe([]);
});

it('ignores non-string values', function (): void {
    $builder = new Builder(new User, '*');

    (new UserScopeFilter)($builder, true, 'scope');

    expect($builder->wheres)->toBe([]);
});
