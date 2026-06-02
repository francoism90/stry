<?php

declare(strict_types=1);

use Domain\Videos\Models\Video;
use Laravel\Scout\Builder;
use Support\Scout\Filters\FiltersUntagged;

it('adds a tagged_count filter when untagged is enabled', function (): void {
    $builder = new Builder(new Video, '*');

    (new FiltersUntagged)($builder, 'true', 'untagged');

    expect($builder->wheres)->toContain([
        'field' => 'tagged_count',
        'operator' => '=',
        'value' => 0,
    ]);
});

it('does not add a filter when untagged is disabled', function (): void {
    $builder = new Builder(new Video, '*');

    (new FiltersUntagged)($builder, 'false', 'untagged');

    expect($builder->wheres)->toBe([]);
});
