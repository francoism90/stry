<?php

declare(strict_types=1);

namespace Domain\Users\DataObjects;

use Spatie\LaravelData\Data;

class UserPlayerSettings extends Data
{
    public function __construct(
        public bool $autoplay = true,
        public bool $muted = false,
        public bool $loop = false,
        public bool $captions = true,
        public string $quality = 'auto',
        public float $playback_speed = 1.0,
        public array $audio_languages = ['en'],
        public array $caption_languages = ['en'],
    ) {}
}
