<?php

declare(strict_types=1);

use Domain\Videos\Actions\GetSimilarVideos;
use Domain\Videos\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
