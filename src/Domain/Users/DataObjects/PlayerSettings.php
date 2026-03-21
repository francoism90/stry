<?php

declare(strict_types=1);

namespace Domain\Users\DataObjects;

use Spatie\LaravelData\Data;

class PlayerSettings extends Data
{
    public function __construct(
        public bool $autoplay = true,
        public bool $muted = false,
        public float $volume = 1.0,
        public bool $loop = false,
        public bool $captions = true,
        public string $quality = 'auto',
        public float $playback_speed = 1.0,
        public string $audio_language = 'en',
        public string $caption_language = 'en',
    ) {}

    /** @return array<string, array<int, string>> */
    public static function rules(): array
    {
        return [
            'autoplay' => ['sometimes', 'boolean'],
            'muted' => ['sometimes', 'boolean'],
            'volume' => ['sometimes', 'numeric', 'min:0', 'max:1'],
            'loop' => ['sometimes', 'boolean'],
            'captions' => ['sometimes', 'boolean'],
            'quality' => ['sometimes', 'string', 'max:255'],
            'playback_speed' => ['sometimes', 'numeric', 'min:0.25', 'max:2'],
            'audio_language' => ['sometimes', 'string', 'max:10'],
            'caption_language' => ['sometimes', 'string', 'max:10'],
        ];
    }
}
