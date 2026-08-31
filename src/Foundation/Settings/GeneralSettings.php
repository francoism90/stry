<?php

declare(strict_types=1);

namespace Foundation\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $site_name = 'My Site';

    public string $timezone = 'Europe/Amsterdam';

    public static function group(): string
    {
        return 'general';
    }
}
