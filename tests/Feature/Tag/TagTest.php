<?php

declare(strict_types=1);

use Domain\Tags\Enums\TagType;
use Domain\Tags\Models\Tag;
use Domain\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create a tag with required attributes', function () {
    $tag = Tag::factory()->create(['name' => ['en' => 'Documentary']]);

    expect($tag->exists)->toBeTrue()
        ->and($tag->name)->toBe('Documentary')
        ->and($tag->slug)->not->toBeEmpty();
});

it('belongs to users through activities', function () {
    $user = User::factory()->create();
    $tag = Tag::factory()->create();

    $tag->activities()->create(['user_id' => $user->getKey()]);

    expect($tag->activities)->toHaveCount(1)
        ->and($tag->activities->first()->user_id)->toBe($user->getKey());
});

it('syncs related tags', function () {
    $tag = Tag::factory()->create();
    $related = Tag::factory()->count(2)->create();

    $tag->syncRelated($related);

    expect($tag->refresh()->relates)->toHaveCount(2);
});

it('can attach tags', function () {
    $tag = Tag::factory()->create();

    $tag->attachTag('Featured');

    expect($tag->tags)->toHaveCount(1)
        ->and($tag->tags->first())->toBeInstanceOf(Tag::class);
});

it('can cast type enum', function () {
    $tag = Tag::factory()->create(['type' => TagType::Genre]);

    expect($tag->type)->toBe(TagType::Genre);
});

it('scopes verified tags by default', function () {
    $verified = Tag::factory()->create();
    Tag::factory()->create(['state' => 'draft']);

    $tags = Tag::query()->verified()->get();

    expect($tags)->toHaveCount(1)
        ->and($tags->first()->is($verified))->toBeTrue();
});
