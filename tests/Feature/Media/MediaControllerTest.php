<?php

declare(strict_types=1);

use App\Web\Media\Controllers\MediaController;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(fn () => Storage::fake('media'));

function createMedia(Video $video, array $attributes = [])
{
    return $video->media()->create([
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

it('updates the name for admins', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');
    $media = createMedia(Video::factory()->create());

    $response = $this->actingAs($user)->put(action([MediaController::class, 'update'], $media), [
        'name' => 'updated-name',
    ]);

    $response->assertRedirect();
    expect($media->refresh()->name)->toBe('updated-name');
});

it('decodes the custom properties json string into an array', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');
    $media = createMedia(Video::factory()->create());

    $response = $this->actingAs($user)->put(action([MediaController::class, 'update'], $media), [
        'custom_properties' => json_encode(['streams' => [['index' => 0, 'codec_name' => 'hevc']]]),
    ]);

    $response->assertRedirect();
    expect($media->refresh()->custom_properties)->toBe([
        'streams' => [['index' => 0, 'codec_name' => 'hevc']],
    ]);
});

it('denies updates for non-admins', function () {
    $user = User::factory()->create();
    $media = createMedia(Video::factory()->create());

    $response = $this->actingAs($user)->put(action([MediaController::class, 'update'], $media), [
        'name' => 'updated-name',
    ]);

    $response->assertForbidden();
});
