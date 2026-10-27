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

    public bool $warm_streams = true;

    public int $import_batch_size = 10;

    public static function group(): string
    {
        return 'processing';
    }
}
