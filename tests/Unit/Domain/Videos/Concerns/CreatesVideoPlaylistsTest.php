<?php

declare(strict_types=1);

use Domain\Playlists\Settings\PlaylistSettings;
use Domain\Shared\Enums\Language;
use Domain\Videos\Actions\CreateNewVideoStream;
use Domain\Videos\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createCaptionMedia(Video $video, array $customProperties = []): void
{
    $video->media()->create([
        'collection_name' => 'captions',
        'name' => 'caption',
        'file_name' => 'caption.vtt',
        'mime_type' => 'text/vtt',
        'disk' => 'media',
        'size' => 1,
        'manipulations' => [],
        'custom_properties' => $customProperties,
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);
}

it('falls back to the configured text language for captions without a language code', function () {
    PlaylistSettings::fake(['text_language' => Language::Dutch]);

    $video = Video::factory()->create();

    createCaptionMedia($video);
    createCaptionMedia($video, ['language_code' => 'de']);

    $action = new class(app(PlaylistSettings::class)) extends CreateNewVideoStream
    {
        public function captionLanguages(Video $video): array
        {
            return $this->getCaptionStreams($video)->pluck('language')->all();
        }
    };

    expect($action->captionLanguages($video->fresh()))->toBe(['nl', 'de']);
});
