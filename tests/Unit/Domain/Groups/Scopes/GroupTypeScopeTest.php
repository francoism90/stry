<?php

declare(strict_types=1);

use Domain\Groups\Models\Group;
use Domain\Groups\Scopes\GroupTypeScope;
use Laravel\Scout\Builder;

it('excludes mixer groups', function (): void {
    $builder = new Builder(new Group, '*');

    (new GroupTypeScope)($builder);

    expect($builder->whereNotIns)->toBe([
        'type' => ['mixer'],
    ]);
});

it('orders by type priority before any other sort', function (): void {
    $builder = new Builder(new Group, '*');

    (new GroupTypeScope)($builder);

    expect($builder->orders)->toBe([
        ['column' => 'type_priority', 'direction' => 'asc'],
    ]);
});
