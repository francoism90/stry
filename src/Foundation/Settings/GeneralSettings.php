<?php

declare(strict_types=1);

namespace Foundation\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $site_name = 'My Site';

    public string $timezone = 'Europe/Amsterdam';

    public string $default_locale = 'en';

    public bool $allow_registration = false;

    public ?int $max_profiles_per_user = null;

    public ?string $maintenance_message = null;

    public static function group(): string
    {
        return 'general';
    }
}
