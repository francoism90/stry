<?php

declare(strict_types=1);

namespace Domain\Videos\Settings;

use Spatie\LaravelSettings\Settings;

class ReelSettings extends Settings
{
    public bool $enabled = false;

    public int $width = 1080;

    public int $height = 1920;

    public int $fps = 30;

    /**
     * The video codec: h264, hevc or av1.
     */
    public string $codec = 'hevc';

    public int $quality = 26;

    public int $cuts = 8;

    public float $cut_duration = 4.0;

    public int $duration = 32;

    public float $scene_threshold = 0.3;

    public static function group(): string
    {
        return 'reels';
    }
}
