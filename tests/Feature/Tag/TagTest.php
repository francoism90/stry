<?php

declare(strict_types=1);

use Domain\Tags\Enums\TagType;
use Domain\Tags\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create a tag with required attributes', function () {
    $tag = Tag::factory()->create(['name' => ['en' => 'Documentary']]);

    expect($tag->exists)->toBeTrue()
        ->and($tag->name)->toBe('Documentary')
        ->and($tag->slug)->not->toBeEmpty();
});

it('syncs related tags', function () {
    $tag = Tag::factory()->create();
    $related = Tag::factory()->count(2)->create();

    $tag->syncRelated($related);

    expect($tag->refresh()->relates)->toHaveCount(2);
});

it('can cast type enum', function () {
    $tag = Tag::factory()->create(['type' => TagType::Genre]);

    expect($tag->type)->toBe(TagType::Genre);
});
