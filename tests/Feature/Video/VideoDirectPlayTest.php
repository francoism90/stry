<?php

declare(strict_types=1);

use Domain\Chapters\Enums\ChapterType;
use Domain\Chapters\Models\Chapter;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Domain\Videos\Settings\PlaybackSettings;
use Domain\Videos\States\Pending;
use Foxws\Media\Encryption\EncryptionKey;
use Foxws\Media\Facades\Media;
use Foxws\Media\Facades\MediaStream;
use Foxws\Media\Testing\FakeProbe;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Modules\Web\Videos\Controllers\VideoController;

beforeEach(function () {
    Storage::fake('media');
    Storage::fake('cache');

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

it('plays the clip directly', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create();
    createDirectPlayClip($video);

    $response = $this->actingAs($user)->get(action([VideoController::class, 'show'], $video));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('playlist.asset', fn (string $url) => str_contains($url, "/api/v1/direct/{$video->getRouteKey()}/dash.mpd?"))
        ->where('playlist.asset_hls', fn (string $url) => str_contains($url, "/api/v1/direct/{$video->getRouteKey()}/cmaf.m3u8?")));
});

it('has nothing to play before the video has a clip', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create();

    $response = $this->actingAs($user)->get(action([VideoController::class, 'show'], $video));

    $response->assertInertia(fn (Assert $page) => $page->where('playlist', null));
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

it('serves the chapters on the stream, with the gaps as the main event', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create();
    createDirectPlayClip($video);
    Chapter::factory()->for($video)->create(['type' => ChapterType::Intro, 'label' => 'Intro', 'start_time' => 0, 'end_time' => 4]);
    Chapter::factory()->for($video)->create(['type' => ChapterType::Credits, 'label' => 'Credits', 'start_time' => 8, 'end_time' => 10]);

    $url = $video->refresh()->chaptersVttUrl();

    expect($url)->toContain("/api/v1/direct/{$video->getRouteKey()}/chapters.vtt?");

    $this->actingAs($user)->get($url)
        ->assertOk()
        ->assertHeader('Content-Type', 'text/vtt; charset=utf-8')
        ->assertSee(["00:00:00.000 --> 00:00:04.000\nIntro", "00:00:04.000 --> 00:00:08.000\nMain Event", "00:00:08.000 --> 00:00:10.000\nCredits", "00:00:10.000 --> 00:00:13.000\nMain Event"], escape: false);
});

it('has no chapters to show for videos without a clip', function () {
    $video = Video::factory()->create();
    Chapter::factory()->for($video)->intro()->create();

    expect($video->refresh()->chaptersVttUrl())->toBeNull();
});

it('offers trick play in the manifest', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create();
    createDirectPlayClip($video);

    $this->actingAs($user)->get(MediaStream::url('videos', ['video' => $video]))
        ->assertOk()
        ->assertSee('#EXT-X-I-FRAME-STREAM-INF');
});

it('encrypts the clip with a clearkey when encryption is on', function () {
    PlaybackSettings::fake(['encryption' => true]);

    $user = User::factory()->create();
    $video = Video::factory()->create();
    createDirectPlayClip($video);
    $key = EncryptionKey::derive(config('app.key'), "video:{$video->getKey()}");

    $manifest = simplexml_load_string((string) $this->actingAs($user)->get(MediaStream::dashUrl('videos', ['video' => $video]))->assertOk()->getContent());
    $licenseUrl = (string) $manifest->Period->AdaptationSet[0]->ContentProtection[1]->children('https://dashif.org/CPS')->Laurl;

    expect((string) $manifest->Period->AdaptationSet[0]->ContentProtection[0]->attributes('urn:mpeg:cenc:2013')['default_KID'])->toBe($key->keyIdUuid());

    $this->actingAs($user)->postJson($licenseUrl, ['kids' => [], 'type' => 'temporary'])
        ->assertOk()
        ->assertExactJson(['keys' => [$key->toJsonWebKey()], 'type' => 'temporary']);
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
