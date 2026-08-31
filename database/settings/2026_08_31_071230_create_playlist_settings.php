<?php

use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->inGroup('playlist', function (SettingsBlueprint $blueprint): void {
            $blueprint->add('type', 'packager');
            $blueprint->add('disk_name', 'segments');
            $blueprint->add('language', 'en');
            $blueprint->add('text_language', 'en');
            $blueprint->add('expires_after', 1209600);
            $blueprint->add('manifest_cache_lifetime', 300);
            $blueprint->add('manifest_url_lifetime', 14400);
            $blueprint->add('manifest_refresh_before', 300);
            $blueprint->add('media_url_lifetime', 14400);
            $blueprint->add('key_url_lifetime', 7200);
            $blueprint->add('encryption', null);
            $blueprint->add('protection_scheme', null);
            $blueprint->add('key_rotation', false);
            $blueprint->add('key_rotation_duration', 300);
        });
    }
};
