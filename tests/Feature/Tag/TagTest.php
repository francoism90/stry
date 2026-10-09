<?php

declare(strict_types=1);

use Domain\Profiles\Models\Profile;
use Domain\Profiles\Support\CurrentProfileContext;
use Domain\Tags\Enums\TagType;
use Domain\Tags\Models\Tag;
use Domain\Videos\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

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

it('eager loads the thumbnail video of each tag in a single query', function () {
    [$documentary, $comedy] = Tag::factory()->count(2)->create()->all();

    $olderDocumentary = Video::factory()->create(['created_at' => now()->subDays(2)]);
    $newerDocumentary = Video::factory()->create(['created_at' => now()->subDay()]);
    $newerComedy = Video::factory()->create(['created_at' => now()]);
    $olderComedy = Video::factory()->create(['created_at' => now()->subDays(3)]);

    collect([$olderDocumentary, $newerDocumentary, $newerComedy, $olderComedy])->each(createClipFor(...));

    $documentary->videos()->attach([$olderDocumentary->getKey(), $newerDocumentary->getKey()]);
    $comedy->videos()->attach([$newerComedy->getKey(), $olderComedy->getKey()]);

    DB::enableQueryLog();

    $tags = Tag::query()->with('thumbs')->whereKey([$documentary->getKey(), $comedy->getKey()])->get()->keyBy('id');

    $thumbnails = $tags->map(fn (Tag $tag) => $tag->thumbnailVideo()?->getKey());

    $tagQueries = collect(DB::getQueryLog())->filter(fn (array $query) => str_contains($query['query'], 'taggables'));

    expect($tagQueries)->toHaveCount(1)
        ->and($thumbnails[$documentary->getKey()])->toBe($newerDocumentary->getKey())
        ->and($thumbnails[$comedy->getKey()])->toBe($newerComedy->getKey());
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
    $tag = Mockery::mock(Tag::class)->makePartial();

    $tag->shouldReceive('avatarUrl')->andReturn('https://cdn.test/poster.jpg');
    $tag->shouldNotReceive('thumbnailVideo');

    expect($tag->thumbnailUrl())->toBe('https://cdn.test/poster.jpg');
});

it('falls back to the thumbnail video without its own picture', function () {
    $video = Mockery::mock(Video::class)->makePartial();
    $video->shouldReceive('getAttribute')->with('thumb')->andReturn('https://cdn.test/frame.jpg');

    $tag = Mockery::mock(Tag::class)->makePartial();

    $tag->shouldReceive('avatarUrl')->andReturnNull();
    $tag->shouldReceive('thumbnailVideo')->andReturn($video);

    expect($tag->thumbnailUrl())->toBe('https://cdn.test/frame.jpg');
});
