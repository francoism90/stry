<?php

declare(strict_types=1);

namespace Domain\Videos\Settings;

use Domain\Shared\Enums\Language;
use Spatie\LaravelSettings\Settings;

class PlaybackSettings extends Settings
{
    public Language $text_language = Language::English;

    public bool $encryption = false;

    public int $refresh_before = 300;

    /**
     * Heights of the smaller renditions to encode next to each clip, e.g. [720, 480].
     *
     * @var list<int>
     */
    public array $renditions = [];

    public static function group(): string
    {
        return 'playback';
    }
}
