<?php

declare(strict_types=1);

use Domain\Tags\Models\Tag;
use Domain\Videos\Actions\UpdateVideoDetails;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\Artisan;
use Spatie\ResponseCache\Facades\ResponseCache;

it('updates video attributes', function () {
    $video = Video::factory()->create([
        'name' => ['en' => 'Original'],
        'snapshot' => 10.0,
    ]);

    app(UpdateVideoDetails::class)->handle($video, [
        'name' => ['en' => 'Updated'],
    ]);

    expect($video->fresh()->name)->toBe('Updated');
});

it('queues media regeneration when snapshot changes', function () {
    Artisan::spy();

    $video = Video::factory()->create(['snapshot' => 10.0]);

    app(UpdateVideoDetails::class)->handle($video, [
        'snapshot' => 20.0,
    ]);

    Artisan::shouldHaveQueued('media-library:regenerate');
});

it('does not queue media regeneration when snapshot does not change', function () {
    Artisan::spy();

    $video = Video::factory()->create(['snapshot' => 10.0]);

    app(UpdateVideoDetails::class)->handle($video, [
        'name' => ['en' => 'New Name'],
    ]);

    Artisan::shouldNotHaveQueued('media-library:regenerate');
});

it('clears the tag response cache when tags are synced', function () {
    $video = Video::factory()->create();
    $tag = Tag::factory()->create();

    ResponseCache::spy();

    app(UpdateVideoDetails::class)->handle($video, [
        'tags' => [['id' => $tag->ulid]],
    ]);

    ResponseCache::shouldHaveReceived('clear')->with(Tag::responseCacheTags());
});

it('does not clear the tag response cache when tags are not provided', function () {
    $video = Video::factory()->create();

    ResponseCache::shouldReceive('clear')->never();

    app(UpdateVideoDetails::class)->handle($video, [
        'name' => ['en' => 'New Name'],
    ]);
});
