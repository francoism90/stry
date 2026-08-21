<?php

declare(strict_types=1);

use Domain\Transcodes\Filters\TranscodeScopeFilter;
use Domain\Transcodes\Models\Transcode;
use Laravel\Scout\Builder;

it('adds a state filter for the pending scope', function (): void {
    $builder = new Builder(new Transcode, '*');

    (new TranscodeScopeFilter)($builder, 'pending', 'scope');

    expect($builder->wheres)->toContain([
        'field' => 'state',
        'operator' => '=',
        'value' => 'pending',
    ]);
});

it('adds a state filter for the processing scope', function (): void {
    $builder = new Builder(new Transcode, '*');

    (new TranscodeScopeFilter)($builder, 'processing', 'scope');

    expect($builder->wheres)->toContain([
        'field' => 'state',
        'operator' => '=',
        'value' => 'processing',
    ]);
});

it('adds a state filter for the completed scope', function (): void {
    $builder = new Builder(new Transcode, '*');

    (new TranscodeScopeFilter)($builder, 'completed', 'scope');

    expect($builder->wheres)->toContain([
        'field' => 'state',
        'operator' => '=',
        'value' => 'completed',
    ]);
});

it('adds a state filter for the failed scope', function (): void {
    $builder = new Builder(new Transcode, '*');

    (new TranscodeScopeFilter)($builder, 'failed', 'scope');

    expect($builder->wheres)->toContain([
        'field' => 'state',
        'operator' => '=',
        'value' => 'failed',
    ]);
});

it('does not add a filter for the all scope', function (): void {
    $builder = new Builder(new Transcode, '*');

    (new TranscodeScopeFilter)($builder, 'all', 'scope');

    expect($builder->wheres)->toBe([]);
});

it('ignores unknown scope values', function (): void {
    $builder = new Builder(new Transcode, '*');

    (new TranscodeScopeFilter)($builder, 'bogus', 'scope');

    expect($builder->wheres)->toBe([]);
});

it('ignores non-string values', function (): void {
    $builder = new Builder(new Transcode, '*');

    (new TranscodeScopeFilter)($builder, true, 'scope');

    expect($builder->wheres)->toBe([]);
});
