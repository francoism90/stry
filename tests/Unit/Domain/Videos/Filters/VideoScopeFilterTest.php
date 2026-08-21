<?php

declare(strict_types=1);

use Domain\Groups\Models\Group;
use Domain\Users\Models\User;
use Domain\Videos\Filters\VideoScopeFilter;
use Domain\Videos\Models\Video;
use Laravel\Scout\Builder;

it('adds a duration filter for the shorts scope', function (): void {
    $builder = new Builder(new Video, '*');

    (new VideoScopeFilter)($builder, 'shorts', 'scope');

    expect($builder->wheres)->toContain([
        'field' => 'duration',
        'operator' => '<=',
        'value' => 300,
    ]);
});

it('adds a tagged_count filter for the untagged scope', function (): void {
    $builder = new Builder(new Video, '*');

    (new VideoScopeFilter)($builder, 'untagged', 'scope');

    expect($builder->wheres)->toContain([
        'field' => 'tagged_count',
        'operator' => '=',
        'value' => 0,
    ]);
});

it('does not add a filter for the all scope', function (): void {
    $builder = new Builder(new Video, '*');

    (new VideoScopeFilter)($builder, 'all', 'scope');

    expect($builder->wheres)->toBe([])
        ->and($builder->callback)->toBeNull();
});

it('ignores unknown scope values', function (): void {
    $builder = new Builder(new Video, '*');

    (new VideoScopeFilter)($builder, 'bogus', 'scope');

    expect($builder->wheres)->toBe([])
        ->and($builder->callback)->toBeNull();
});

it('ignores non-string values', function (): void {
    $builder = new Builder(new Video, '*');

    (new VideoScopeFilter)($builder, true, 'scope');

    expect($builder->wheres)->toBe([])
        ->and($builder->callback)->toBeNull();
});

it('excludes the viewed group from results for the unseen scope', function (): void {
    $user = User::factory()->create();
    $group = Group::factory()->viewed()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user);

    $builder = new Builder(new Video, '*');

    (new VideoScopeFilter)($builder, 'unseen', 'scope');

    expect($builder->callback)->toBeInstanceOf(Closure::class);

    $options = ($builder->callback)(
        new class
        {
            public function search($options)
            {
                return $options;
            }
        },
        $builder,
        [],
    );

    expect($options['filter_by'])->toContain('$groupables(group_id:');
});

it('does not add a callback for the unseen scope without an authenticated user', function (): void {
    $builder = new Builder(new Video, '*');

    (new VideoScopeFilter)($builder, 'unseen', 'scope');

    expect($builder->callback)->toBeNull();
});

it('does not add a callback for the unseen scope without a viewed group', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $builder = new Builder(new Video, '*');

    (new VideoScopeFilter)($builder, 'unseen', 'scope');

    expect($builder->callback)->toBeNull();
});
