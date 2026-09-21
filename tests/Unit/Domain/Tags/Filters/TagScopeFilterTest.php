<?php

declare(strict_types=1);

use Domain\Tags\Enums\TagScope;
use Domain\Tags\Filters\TagScopeFilter;
use Domain\Tags\Models\Tag;
use Laravel\Scout\Builder;

it('adds a type filter for each tag type scope', function (string $value): void {
    $builder = new Builder(new Tag, '*');

    (new TagScopeFilter)($builder, $value, 'scope');

    expect($builder->wheres)->toContain([
        'field' => 'type',
        'operator' => '=',
        'value' => $value,
    ]);
})->with(
    collect(TagScope::cases())
        ->reject(fn (TagScope $scope) => $scope === TagScope::All)
        ->map(fn (TagScope $scope) => $scope->value)
        ->all()
);

it('does not filter the all scope', function (): void {
    $builder = new Builder(new Tag, '*');

    (new TagScopeFilter)($builder, 'all', 'scope');

    expect($builder->wheres)->toBe([]);
});

it('ignores unknown scope values', function (): void {
    $builder = new Builder(new Tag, '*');

    (new TagScopeFilter)($builder, 'bogus', 'scope');

    expect($builder->wheres)->toBe([]);
});

it('ignores non-string values', function (): void {
    $builder = new Builder(new Tag, '*');

    (new TagScopeFilter)($builder, true, 'scope');

    expect($builder->wheres)->toBe([]);
});
