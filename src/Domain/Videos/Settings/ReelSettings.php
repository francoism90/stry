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
     * How the picture fits the frame: crop (zoomed by $zoom, on a blurred copy of itself) or letterbox.
     */
    public string $fit = 'crop';

    /**
     * How much of the frame a cropped picture fills (0-100): 100 fills it all, 0 shows the whole picture.
     */
    public int $zoom = 50;

    /**
     * The video codec: h264, hevc or av1.
     */
    public string $codec = 'h264';

    /**
     * Encode on the GPU in media.ladder.hardware instead of the CPU. Some GPUs, such as AMD with VAAPI,
     * write broken HEVC.
     */
    public bool $hardware = false;

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
