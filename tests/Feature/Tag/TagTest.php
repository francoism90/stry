<?php

declare(strict_types=1);

use Domain\Profiles\Models\Profile;
use Domain\Profiles\Support\CurrentProfileContext;
use Domain\Tags\Enums\TagType;
use Domain\Tags\Models\Tag;
use Domain\Videos\Models\Video;
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

it('uses the newest tagged video with a clip as its thumbnail video', function () {
    $tag = Tag::factory()->create();

    $older = Video::factory()->create(['created_at' => now()->subDay()]);
    $newer = Video::factory()->create(['created_at' => now()]);
    $newestWithoutClip = Video::factory()->create(['created_at' => now()->addDay()]);

    createClipFor($older);
    createClipFor($newer);

    $tag->videos()->attach([$older->getKey(), $newer->getKey(), $newestWithoutClip->getKey()]);

    expect($tag->thumbnailVideo()?->getKey())->toBe($newer->getKey());
});

it('has no thumbnail video when none of its videos has a clip', function () {
    $tag = Tag::factory()->create();

    $tag->videos()->attach(Video::factory()->create());

    expect($tag->thumbnailVideo())->toBeNull()
        ->and($tag->thumb)->toBeNull();
});

it('skips adult videos for the thumbnail on a kids profile', function () {
    $tag = Tag::factory()->create();

    $safe = Video::factory()->create(['adult' => false, 'created_at' => now()->subDay()]);
    $adult = Video::factory()->create(['adult' => true, 'created_at' => now()]);

    createClipFor($safe);
    createClipFor($adult);

    $tag->videos()->attach([$safe->getKey(), $adult->getKey()]);

    app(CurrentProfileContext::class)->set(Profile::factory()->create(['is_kids' => true]));

    expect($tag->thumbnailVideo()?->getKey())->toBe($safe->getKey());
});

it('prefers its own picture over the thumbnail video', function () {
    $tag = Tag::factory()->create();

    $video = Video::factory()->create();
    createClipFor($video);
    $tag->videos()->attach($video);

    $tag->media()->create([
        'collection_name' => 'avatar',
        'name' => 'avatar',
        'file_name' => 'avatar.jpg',
        'mime_type' => 'image/jpeg',
        'disk' => 'conversions',
        'conversions_disk' => 'conversions',
        'size' => 1,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => ['thumb' => true],
        'responsive_images' => [],
    ]);

    expect($tag->thumb)->toBeString()->toContain('avatar')
        ->and($tag->thumb)->not->toBe($video->thumb);
});
