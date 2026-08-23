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

function createVideoStoryboardMedia(Video $video, string $mimeType, string $fileName): void
{
    $video->media()->create([
        'collection_name' => 'storyboards',
        'name' => 'storyboard',
        'file_name' => $fileName,
        'mime_type' => $mimeType,
        'disk' => 'media',
        'size' => 1,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);
}

it('exposes null storyboard urls when the video has no storyboard media', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');
    $video = Video::factory()->create();

    $response = $this->actingAs($user)->get(action([VideoController::class, 'show'], $video));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('video.storyboard_image', null)
        ->where('video.storyboard_vtt', null));
});

it('exposes signed storyboard urls when the video has storyboard media', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');
    $video = Video::factory()->create();
    createVideoStoryboardMedia($video, 'image/jpeg', 'storyboard.jpg');
    createVideoStoryboardMedia($video, 'text/vtt', 'storyboard.vtt');

    $response = $this->actingAs($user)->get(action([VideoController::class, 'show'], $video));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('video.storyboard_image', fn (?string $url) => filled($url))
        ->where('video.storyboard_vtt', fn (?string $url) => filled($url)));
});
