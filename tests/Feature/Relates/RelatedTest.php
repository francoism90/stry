<?php

declare(strict_types=1);

use Domain\Relates\Models\Related;
use Domain\Tags\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates related record when attaching models', function () {
    $tag = Tag::factory()->create();
    $relatedTag = Tag::factory()->create();

    $tag->attachRelated($relatedTag);

    $relatedRecord = Related::first();

    expect(Related::count())->toBe(1)
        ->and($relatedRecord)->not->toBeNull()
        ->and($relatedRecord->relatable->is($tag))->toBeTrue()
        ->and($relatedRecord->model->is($relatedTag))->toBeTrue();
});

it('syncs related models by removing stale relations', function () {
    $tag = Tag::factory()->create();
    $related = Tag::factory()->count(2)->create();

    $tag->syncRelated($related);
    $tag->syncRelated($related->take(1)->values());

    expect(Related::count())->toBe(1)
        ->and($tag->fresh()->relates)->toHaveCount(1)
        ->and($tag->fresh()->relates->first()->is($related->first()))->toBeTrue();
});

it('removes related records when deleting a model', function () {
    $tag = Tag::factory()->create();
    $relatedTag = Tag::factory()->create();

    $tag->attachRelated($relatedTag);

    expect(Related::count())->toBe(1);

    $tag->delete();

    expect(Related::count())->toBe(0);
});
