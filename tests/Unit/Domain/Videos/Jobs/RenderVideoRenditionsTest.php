<?php

declare(strict_types=1);

use Domain\Videos\Actions\CreateClipRenditions;
use Domain\Videos\Jobs\RenderVideoRenditions;
use Domain\Videos\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('runs on the processing queue of the long connection', function () {
    $job = new RenderVideoRenditions(Video::factory()->create());

    expect([$job->connection, $job->queue])->toBe(['redis-long', 'processing']);
});

it('skips videos without a clip', function () {
    $renditions = Mockery::mock(CreateClipRenditions::class);
    $renditions->shouldNotReceive('handle');

    (new RenderVideoRenditions(Video::factory()->create()))->handle($renditions);
});
