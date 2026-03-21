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
}
