<?php

declare(strict_types=1);

use Domain\Videos\Actions\GetSimilarVideos;
use Domain\Videos\Collections\VideoCollection;
use Domain\Videos\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;

uses(RefreshDatabase::class);

it('returns next episode first when current is mid-series', function () {
    $name = ['en' => 'My Series'];

    $s01e01 = Video::factory()->create(['name' => $name, 'season' => '1', 'episode' => '1', 'part' => null]);
    $s01e02 = Video::factory()->create(['name' => $name, 'season' => '1', 'episode' => '2', 'part' => null]);
    $s01e03 = Video::factory()->create(['name' => $name, 'season' => '1', 'episode' => '3', 'part' => null]);
    $s01e04 = Video::factory()->create(['name' => $name, 'season' => '1', 'episode' => '4', 'part' => null]);

    $results = app(GetSimilarVideos::class)->handle($s01e02, 10);

    expect($results->first()->id)->toBe($s01e03->id)
        ->and($results->last()->id)->toBe($s01e01->id);
});

it('wraps around to earlier episodes when on the last episode', function () {
    $name = ['en' => 'My Series'];

    $s01e01 = Video::factory()->create(['name' => $name, 'season' => '1', 'episode' => '1', 'part' => null]);
    $s01e02 = Video::factory()->create(['name' => $name, 'season' => '1', 'episode' => '2', 'part' => null]);
    $s01e03 = Video::factory()->create(['name' => $name, 'season' => '1', 'episode' => '3', 'part' => null]);

    $results = app(GetSimilarVideos::class)->handle($s01e03, 10);

    expect($results->pluck('id')->first())->toBe($s01e01->id);
});

it('orders parts correctly within the same episode', function () {
    $name = ['en' => 'My Series'];

    $part1 = Video::factory()->create(['name' => $name, 'season' => '1', 'episode' => '1', 'part' => '1']);
    $part2 = Video::factory()->create(['name' => $name, 'season' => '1', 'episode' => '1', 'part' => '2']);
    $part3 = Video::factory()->create(['name' => $name, 'season' => '1', 'episode' => '1', 'part' => '3']);

    $results = app(GetSimilarVideos::class)->handle($part2, 10);

    expect($results->first()->id)->toBe($part3->id)
        ->and($results->last()->id)->toBe($part1->id);
});

it('handles videos with null season, episode, and part without errors', function () {
    $name = ['en' => 'My Series'];

    $current = Video::factory()->create(['name' => $name, 'season' => null, 'episode' => null, 'part' => null]);
    $other = Video::factory()->create(['name' => $name, 'season' => null, 'episode' => null, 'part' => null]);

    $results = app(GetSimilarVideos::class)->handle($current, 10);

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($other->id);
});

it('returns an empty collection when the video name is blank', function () {
    $video = Video::factory()->create(['name' => ['en' => '']]);

    $results = app(GetSimilarVideos::class)->handle($video, 10);

    expect($results)->toBeEmpty();
});

it('does not include the current video in results', function () {
    $name = ['en' => 'My Series'];

    $current = Video::factory()->create(['name' => $name, 'season' => '1', 'episode' => '1', 'part' => null]);
    Video::factory()->create(['name' => $name, 'season' => '1', 'episode' => '2', 'part' => null]);

    $results = app(GetSimilarVideos::class)->handle($current, 10);

    expect($results->pluck('id'))->not->toContain($current->id);
});

it('includes same-name videos without season, episode, or part as fallback', function () {
    $name = ['en' => 'My Series'];

    $s01e02 = Video::factory()->create(['name' => $name, 'season' => '1', 'episode' => '2', 'part' => null]);
    $s01e03 = Video::factory()->create(['name' => $name, 'season' => '1', 'episode' => '3', 'part' => null]);
    $standalone = Video::factory()->create(['name' => $name, 'season' => null, 'episode' => null, 'part' => null]);

    $results = app(GetSimilarVideos::class)->handle($s01e02, 10);

    expect($results->pluck('id'))
        ->toContain($standalone->id)
        ->and($results->first()->id)->toBe($s01e03->id)
        ->and($results->last()->id)->toBe($standalone->id);
});

it('surfaces the next season before previous season episodes', function () {
    $name = ['en' => 'My Series'];

    $s01e05 = Video::factory()->create(['name' => $name, 'season' => '1', 'episode' => '5', 'part' => null]);
    Video::factory()->create(['name' => $name, 'season' => '1', 'episode' => '1', 'part' => null]);
    $s02e01 = Video::factory()->create(['name' => $name, 'season' => '2', 'episode' => '1', 'part' => null]);

    $results = app(GetSimilarVideos::class)->handle($s01e05, 10);

    expect($results->first()->id)->toBe($s02e01->id);
});

it('finds series videos when the video name is stored only in the fallback locale', function () {
    App::setLocale('fr');

    $name = ['en' => 'My Series'];

    $current = Video::factory()->create(['name' => $name, 'season' => '1', 'episode' => '1', 'part' => null]);
    $other = Video::factory()->create(['name' => $name, 'season' => '1', 'episode' => '2', 'part' => null]);

    $results = app(GetSimilarVideos::class)->handle($current, 10);

    expect($results->pluck('id'))->toContain($other->id);
});

it('excludes unverified videos from series matches', function () {
    $name = ['en' => 'My Series'];

    $current = Video::factory()->create(['name' => $name, 'season' => '1', 'episode' => '1', 'part' => null]);
    $pending = Video::factory()->pending()->create(['name' => $name, 'season' => '1', 'episode' => '2', 'part' => null]);

    $results = app(GetSimilarVideos::class)->handle($current, 10);

    expect($results->pluck('id'))->not->toContain($pending->id);
});

it('finds videos that share tags', function () {
    $current = Video::factory()->create();
    $current->attachTag('action');

    $withTag = Video::factory()->create();
    $withTag->attachTag('action');

    $results = app(GetSimilarVideos::class)->handle($current, 10);

    expect($results->pluck('id'))->toContain($withTag->id);
});

it('excludes unverified videos from tag matches', function () {
    $current = Video::factory()->create();
    $current->attachTag('drama');

    $pending = Video::factory()->pending()->create();
    $pending->attachTag('drama');

    $results = app(GetSimilarVideos::class)->handle($current, 10);

    expect($results->pluck('id'))->not->toContain($pending->id);
});

it('respects the limit', function () {
    $name = ['en' => 'Long Series'];

    $current = Video::factory()->create(['name' => $name, 'season' => '1', 'episode' => '1', 'part' => null]);
    Video::factory()->count(20)->create(['name' => $name]);

    $results = app(GetSimilarVideos::class)->handle($current, 5);

    expect($results)->toHaveCount(5);
});

it('returns a VideoCollection instance', function () {
    $video = Video::factory()->create();

    $results = app(GetSimilarVideos::class)->handle($video, 10);

    expect($results)->toBeInstanceOf(VideoCollection::class);
});

it('deduplicates videos that appear in multiple strategies', function () {
    $name = ['en' => 'My Series'];
    $tag = 'thriller';

    $current = Video::factory()->create(['name' => $name]);
    $current->attachTag($tag);

    // This video matches both by series name and by tag
    $shared = Video::factory()->create(['name' => $name]);
    $shared->attachTag($tag);

    $results = app(GetSimilarVideos::class)->handle($current, 10);

    expect($results->pluck('id')->filter(fn (mixed $id) => $id === $shared->id))->toHaveCount(1);
});
