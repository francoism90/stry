<?php

declare(strict_types=1);

use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Modules\Web\Videos\Controllers\VideoController;

beforeEach(fn () => Storage::fake('media'));

it('defers the disks a video can be moved to, marking the current one, for admins', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');
    $video = Video::factory()->create();
    createClipFor($video);

    $response = $this->actingAs($user)->get(action([VideoController::class, 'show'], $video));

    $response->assertInertia(fn (Assert $page) => $page
        ->missing('disks')
        ->loadDeferredProps(fn (Assert $reload) => $reload
            ->has('disks', 1)
            ->where('disks.0.value', 'media')
            ->where('disks.0.current', true)
        ));
});

it('gives other users no disks', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create();

    $response = $this->actingAs($user)->get(action([VideoController::class, 'show'], $video));

    $response->assertInertia(fn (Assert $page) => $page
        ->loadDeferredProps(fn (Assert $reload) => $reload->where('disks', [])));
});
