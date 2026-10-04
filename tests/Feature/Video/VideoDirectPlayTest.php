<?php

declare(strict_types=1);

use Domain\Playlists\Enums\PlaybackMode;
use Domain\Playlists\Settings\PlaylistSettings;
use Domain\Users\Models\User;
use Domain\Videos\Jobs\PlaylistVideo;
use Domain\Videos\Models\Video;
use Domain\Videos\States\Pending;
use Foxws\Media\Facades\Media;
use Foxws\Media\Facades\MediaStream;
use Foxws\Media\Testing\FakeProbe;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Modules\Web\Videos\Controllers\VideoController;

beforeEach(function () {
    Storage::fake('media');
    Storage::fake('segments');

    Media::fake(['clip.mp4' => FakeProbe::video(duration: 13)]);
});

function createDirectPlayClip(Video $video): void
{
    $clip = $video->media()->create([
        'collection_name' => 'clips',
        'name' => 'clip',
        'file_name' => 'clip.mp4',
        'mime_type' => 'video/mp4',
        'disk' => 'media',
        'size' => 5,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);

    Storage::disk('media')->put($clip->getPathRelativeToRoot(), 'video');
}

it('plays the clip directly instead of packaging a playlist when direct play is on', function () {
    PlaylistSettings::fake(['type' => PlaybackMode::Direct]);

    $user = User::factory()->create();
    $video = Video::factory()->create();
    createDirectPlayClip($video);

    $response = $this->actingAs($user)->get(action([VideoController::class, 'show'], $video));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('playlist.type', 'direct')
        ->where('playlist.valid', true)
        ->where('playlist.asset', fn (string $url) => str_contains($url, "/api/v1/direct/{$video->getRouteKey()}/dash.mpd?"))
        ->where('playlist.asset_hls', fn (string $url) => str_contains($url, "/api/v1/direct/{$video->getRouteKey()}/cmaf.m3u8?")));

    Bus::assertNotDispatched(PlaylistVideo::class);
});

it('keeps packaging playlists in the other playback modes', function () {
    PlaylistSettings::fake(['type' => PlaybackMode::Packager]);

    $user = User::factory()->create();
    $video = Video::factory()->create();
    createDirectPlayClip($video);

    $response = $this->actingAs($user)->get(action([VideoController::class, 'show'], $video));

    $response->assertInertia(fn (Assert $page) => $page->where('playlist', null));

    Bus::assertDispatched(PlaylistVideo::class);
});

it('serves the dash manifest and hls playlist of the clip on a signed url', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create();
    createDirectPlayClip($video);

    $this->actingAs($user)->get(MediaStream::dashUrl('videos', ['video' => $video]))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/dash+xml');

    $this->actingAs($user)->get(MediaStream::url('videos', ['video' => $video]))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/vnd.apple.mpegurl');
});

it('refuses unsigned requests', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create();
    createDirectPlayClip($video);

    $this->actingAs($user)->get(route('api.media.videos.dash', ['video' => $video]))->assertForbidden();
});

it('refuses videos the user may not view', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create(['state' => Pending::class]);
    createDirectPlayClip($video);

    $this->actingAs($user)->get(MediaStream::dashUrl('videos', ['video' => $video]))->assertForbidden();
});
