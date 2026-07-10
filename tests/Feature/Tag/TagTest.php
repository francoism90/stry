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

it('returns synonyms as a list in the searchable array', function () {
    $tag = Tag::factory()->create();
    // An empty description in a non-trailing position leaves a gap in the
    // underlying array once filtered, which json_encode() would otherwise
    // serialize as a JSON object instead of an array.
    $related = [
        Tag::factory()->create(['description' => ['en' => '']]),
        Tag::factory()->create(),
    ];

    $tag->syncRelated($related);
    $tag->refresh();

    $synonyms = $tag->toSearchableArray()['synonyms'];

    expect($synonyms)->toBeArray()
        ->and(array_is_list($synonyms))->toBeTrue();
});

it('returns translated as a list in the searchable array', function () {
    $tag = Tag::factory()->create([
        'name' => ['en' => 'Documentary', 'es' => 'Documental'],
        'description' => ['en' => '', 'es' => 'Un film'],
    ]);

    $translated = $tag->toSearchableArray()['translated'];

    expect($translated)->toBeArray()
        ->and(array_is_list($translated))->toBeTrue()
        ->and($translated)->toContain('Documentary', 'Documental', 'Un film');
});
