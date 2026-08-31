<?php

use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->inGroup('general', function (SettingsBlueprint $blueprint): void {
            $blueprint->add('site_name', 'My Site');
            $blueprint->add('timezone', 'Europe/Amsterdam');
            $blueprint->add('default_locale', 'en-US');
            $blueprint->add('allow_registration', false);
            $blueprint->add('max_profiles_per_user', null);
            $blueprint->add('maintenance_message', null);
        });
    }
};
