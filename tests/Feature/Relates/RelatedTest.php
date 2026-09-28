<?php

declare(strict_types=1);

use Domain\Tags\Models\Tag;
use Foxws\Relatable\Models\Relatable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates related record when attaching models', function () {
    $tag = Tag::factory()->create();
    $relatedTag = Tag::factory()->create();

    $tag->attachRelated($relatedTag);

    $relatedRecord = Relatable::first();

    expect(Relatable::count())->toBe(1)
        ->and($relatedRecord)->not->toBeNull()
        ->and($relatedRecord->relatable->is($tag))->toBeTrue()
        ->and($relatedRecord->related->is($relatedTag))->toBeTrue();
});

it('syncs related models by removing stale relations', function () {
    $tag = Tag::factory()->create();
    $related = Tag::factory()->count(2)->create();

    $tag->syncRelated($related);
    $tag->syncRelated($related->take(1)->values());

    expect(Relatable::count())->toBe(1)
        ->and($tag->fresh()->relates)->toHaveCount(1)
        ->and($tag->fresh()->relates->first()->is($related->first()))->toBeTrue();
});

it('does not duplicate relations when syncing identical models', function () {
    $tag = Tag::factory()->create();
    $relatedTag = Tag::factory()->create();

    $tag->syncRelated([$relatedTag]);
    $tag->syncRelated([$relatedTag]);

    expect(Relatable::count())->toBe(1)
        ->and($tag->fresh()->relates)->toHaveCount(1)
        ->and($tag->fresh()->relates->first()->is($relatedTag))->toBeTrue();
});

it('removes related records when deleting a model', function () {
    $tag = Tag::factory()->create();
    $relatedTag = Tag::factory()->create();

    $tag->attachRelated($relatedTag);

    expect(Relatable::count())->toBe(1);

    $tag->delete();

    expect(Relatable::count())->toBe(0);
});

it('orders related models by weight', function () {
    $tag = Tag::factory()->create();
    [$fastpace, $foo, $chase] = Tag::factory()->count(3)->create();

    $tag->attachRelated($foo, score: 0.5);
    $tag->attachRelated($chase, score: 0.4, boost: 2.0);
    $tag->attachRelated($fastpace, score: 1.0);

    expect($tag->fresh()->relates->map->getKey()->all())->toBe([$fastpace->getKey(), $chase->getKey(), $foo->getKey()]);
});
