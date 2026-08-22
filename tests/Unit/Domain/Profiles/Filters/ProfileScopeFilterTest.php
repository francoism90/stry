<?php

declare(strict_types=1);

use Domain\Profiles\Filters\ProfileScopeFilter;
use Domain\Profiles\Models\Profile;
use Laravel\Scout\Builder;

it('adds an is_kids filter for the kids scope', function (): void {
    $builder = new Builder(new Profile, '*');

    (new ProfileScopeFilter)($builder, 'kids', 'scope');

    expect($builder->wheres)->toContain([
        'field' => 'is_kids',
        'operator' => '=',
        'value' => true,
    ]);
});

it('adds an is_primary filter for the primary scope', function (): void {
    $builder = new Builder(new Profile, '*');

    (new ProfileScopeFilter)($builder, 'primary', 'scope');

    expect($builder->wheres)->toContain([
        'field' => 'is_primary',
        'operator' => '=',
        'value' => true,
    ]);
});

it('does not add a filter for the all scope', function (): void {
    $builder = new Builder(new Profile, '*');

    (new ProfileScopeFilter)($builder, 'all', 'scope');

    expect($builder->wheres)->toBe([]);
});

it('ignores unknown scope values', function (): void {
    $builder = new Builder(new Profile, '*');

    (new ProfileScopeFilter)($builder, 'bogus', 'scope');

    expect($builder->wheres)->toBe([]);
});

it('ignores non-string values', function (): void {
    $builder = new Builder(new Profile, '*');

    (new ProfileScopeFilter)($builder, true, 'scope');

    expect($builder->wheres)->toBe([]);
});
