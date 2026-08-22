<?php

declare(strict_types=1);

use Domain\Videos\Filters\VideoLibraryScopeFilter;
use Domain\Videos\Models\Video;
use Laravel\Scout\Builder;

it('adds a state filter for the verified scope', function (): void {
    $builder = new Builder(new Video, '*');

    (new VideoLibraryScopeFilter)($builder, 'verified', 'scope');

    expect($builder->wheres)->toContain([
        'field' => 'state',
        'operator' => '=',
        'value' => 'verified',
    ]);
});

it('adds a state filter for the pending scope', function (): void {
    $builder = new Builder(new Video, '*');

    (new VideoLibraryScopeFilter)($builder, 'pending', 'scope');

    expect($builder->wheres)->toContain([
        'field' => 'state',
        'operator' => '=',
        'value' => 'pending',
    ]);
});

it('adds a state filter for the failed scope', function (): void {
    $builder = new Builder(new Video, '*');

    (new VideoLibraryScopeFilter)($builder, 'failed', 'scope');

    expect($builder->wheres)->toContain([
        'field' => 'state',
        'operator' => '=',
        'value' => 'failed',
    ]);
});

it('does not add a filter for the all scope', function (): void {
    $builder = new Builder(new Video, '*');

    (new VideoLibraryScopeFilter)($builder, 'all', 'scope');

    expect($builder->wheres)->toBe([]);
});

it('ignores unknown scope values', function (): void {
    $builder = new Builder(new Video, '*');

    (new VideoLibraryScopeFilter)($builder, 'bogus', 'scope');

    expect($builder->wheres)->toBe([]);
});

it('ignores non-string values', function (): void {
    $builder = new Builder(new Video, '*');

    (new VideoLibraryScopeFilter)($builder, true, 'scope');

    expect($builder->wheres)->toBe([]);
});
