<?php

declare(strict_types=1);

use App\Web\Videos\Controllers\VideoController;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(fn () => Storage::fake('media'));

function createVideoMedia(Video $video, array $attributes = []): void
{
    $video->media()->create([
        'collection_name' => 'clips',
        'name' => 'clip',
        'file_name' => 'clip.mp4',
        'mime_type' => 'video/mp4',
        'disk' => 'media',
        'size' => 1,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
        ...$attributes,
    ]);
}

it('defers the media prop with the video media for admins', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');
    $video = Video::factory()->create();
    createVideoMedia($video, ['name' => 'my-clip']);

    $response = $this->actingAs($user)->get(action([VideoController::class, 'show'], $video));

    $response->assertInertia(fn (Assert $page) => $page
        ->missing('media')
        ->loadDeferredProps(fn (Assert $reload) => $reload
            ->has('media', 1)
            ->where('media.0.name', 'my-clip')
        ));
});

it('limits the media prop to the ten most recent media items', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');
    $video = Video::factory()->create();

    for ($i = 0; $i < 12; $i++) {
        createVideoMedia($video);
    }

    $response = $this->actingAs($user)->get(action([VideoController::class, 'show'], $video));

    $response->assertInertia(fn (Assert $page) => $page
        ->loadDeferredProps(fn (Assert $reload) => $reload
            ->has('media', 10)
        ));
});

it('does not include media belonging to other videos', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');
    $video = Video::factory()->create();
    $other = Video::factory()->create();
    createVideoMedia($other);

    $response = $this->actingAs($user)->get(action([VideoController::class, 'show'], $video));

    $response->assertInertia(fn (Assert $page) => $page
        ->loadDeferredProps(fn (Assert $reload) => $reload
            ->has('media', 0)
        ));
});

it('returns no media for users without permission to view any media', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create();
    createVideoMedia($video);

    $response = $this->actingAs($user)->get(action([VideoController::class, 'show'], $video));

    $response->assertInertia(fn (Assert $page) => $page
        ->loadDeferredProps(fn (Assert $reload) => $reload
            ->has('media', 0)
        ));
});
