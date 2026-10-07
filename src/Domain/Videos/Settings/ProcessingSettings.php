<?php

declare(strict_types=1);

namespace Domain\Videos\Settings;

use Spatie\LaravelSettings\Settings;

class ProcessingSettings extends Settings
{
    public bool $extract_captions = true;

    public bool $extract_chapters = true;

    public bool $extract_storyboard = true;

    public bool $create_renditions = false;

    public bool $create_reels = false;

    public int $reel_width = 1080;

    public int $reel_height = 1920;

    public int $reel_fps = 30;

    public static function group(): string
    {
        return 'processing';
    }
}
