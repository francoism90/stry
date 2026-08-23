<?php

declare(strict_types=1);

use Domain\Groups\Filters\GroupScopeFilter;
use Domain\Groups\Models\Group;
use Laravel\Scout\Builder;

it('adds a type filter for the custom scope', function (): void {
    $builder = new Builder(new Group, '*');

    (new GroupScopeFilter)($builder, 'custom', 'scope');

    expect($builder->wheres)->toContain([
        'field' => 'type',
        'operator' => '=',
        'value' => 'custom',
    ]);
});

it('adds a type filter for the mixer scope', function (): void {
    $builder = new Builder(new Group, '*');

    (new GroupScopeFilter)($builder, 'mixer', 'scope');

    expect($builder->wheres)->toContain([
        'field' => 'type',
        'operator' => '=',
        'value' => 'mixer',
    ]);
});

it('excludes mixer groups for the all scope', function (): void {
    $builder = new Builder(new Group, '*');

    (new GroupScopeFilter)($builder, 'all', 'scope');

    expect($builder->whereNotIns)->toBe([
        'type' => ['mixer'],
    ]);
});

it('excludes mixer groups for unknown scope values', function (): void {
    $builder = new Builder(new Group, '*');

    (new GroupScopeFilter)($builder, 'bogus', 'scope');

    expect($builder->whereNotIns)->toBe([
        'type' => ['mixer'],
    ]);
});

it('ignores non-string values', function (): void {
    $builder = new Builder(new Group, '*');

    (new GroupScopeFilter)($builder, true, 'scope');

    expect($builder->wheres)->toBe([]);
});
