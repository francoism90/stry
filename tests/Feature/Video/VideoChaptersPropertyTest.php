<?php

declare(strict_types=1);

use App\Web\Videos\Controllers\VideoController;
use Domain\Chapters\Enums\ChapterType;
use Domain\Chapters\Models\Chapter;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(fn () => Storage::fake('conversions'));

function createVideoChaptersMedia(Video $video): void
{
    $video->media()->create([
        'collection_name' => 'chapters',
        'name' => 'chapters',
        'file_name' => 'chapters.vtt',
        'mime_type' => 'text/vtt',
        'disk' => 'conversions',
        'size' => 1,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);
}

it('exposes an empty chapter list and a null vtt url when the video has no chapters', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');
    $video = Video::factory()->create();

    $response = $this->actingAs($user)->get(action([VideoController::class, 'show'], $video));

    $response->assertInertia(fn (Assert $page) => $page
        ->has('video.chapters', 0)
        ->where('video.chapters_vtt', null));
});

it('exposes the video chapters when the video has chapters', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');
    $video = Video::factory()->create();
    Chapter::factory()->intro()->create(['video_id' => $video->getKey()]);

    $response = $this->actingAs($user)->get(action([VideoController::class, 'show'], $video));

    $response->assertInertia(fn (Assert $page) => $page
        ->has('video.chapters', 1)
        ->where('video.chapters.0.type', ChapterType::Intro->value)
        ->where('video.chapters.0.skippable', true));
});

it('exposes a signed vtt url when the video has a generated chapters file', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');
    $video = Video::factory()->create();
    createVideoChaptersMedia($video);

    $response = $this->actingAs($user)->get(action([VideoController::class, 'show'], $video));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('video.chapters_vtt', fn (?string $url) => filled($url)));
});

it('does not include chapters belonging to other videos', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');
    $video = Video::factory()->create();
    $other = Video::factory()->create();
    Chapter::factory()->create(['video_id' => $other->getKey()]);

    $response = $this->actingAs($user)->get(action([VideoController::class, 'show'], $video));

    $response->assertInertia(fn (Assert $page) => $page
        ->has('video.chapters', 0));
});
