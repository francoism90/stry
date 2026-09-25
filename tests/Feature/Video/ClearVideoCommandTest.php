<?php

declare(strict_types=1);

use Domain\Videos\Models\Video;

it('force deletes trashed videos on confirmation', function () {
    $video = Video::factory()->create();
    $video->delete();

    $this->artisan('videos:clear')
        ->expectsConfirmation('Are you sure you want to force-delete these 1 videos?', 'yes')
        ->assertSuccessful();

    expect(Video::withTrashed()->count())->toBe(0);
});

it('force deletes trashed videos without confirmation when using --force', function () {
    $video = Video::factory()->create();
    $video->delete();

    $this->artisan('videos:clear --force')
        ->assertSuccessful();

    expect(Video::withTrashed()->count())->toBe(0);
});
