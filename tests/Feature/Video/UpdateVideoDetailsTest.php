<?php

declare(strict_types=1);

use Domain\Videos\Actions\UpdateVideoDetails;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\Artisan;

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
