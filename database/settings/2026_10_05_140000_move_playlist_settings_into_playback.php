<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Spatie\LaravelSettings\Support\SettingsCacheFactory;

return new class extends SettingsMigration
{
    /**
     * Direct play is the only playback mode now, so the playlist group makes way for a playback
     * group that keeps the subtitle language, whether to encrypt and when to refresh the URLs.
     */
    public function up(): void
    {
        $this->migrator->add('playback.text_language', $this->pull('text_language', 'en'));
        $this->migrator->add('playback.encryption', $this->pull('encryption') !== null);
        $this->migrator->add('playback.refresh_before', $this->pull('manifest_refresh_before', 300));

        foreach (['type', 'disk_name', 'language', 'expires_after', 'manifest_cache_lifetime', 'manifest_url_lifetime', 'media_url_lifetime', 'key_url_lifetime', 'protection_scheme', 'key_rotation', 'key_rotation_duration'] as $name) {
            $this->migrator->deleteIfExists("playlist.{$name}");
        }

        foreach (app(SettingsCacheFactory::class)->all() as $settingsCache) {
            $settingsCache->clear();
        }
    }

    protected function pull(string $name, mixed $default = null): mixed
    {
        if (! $this->migrator->exists("playlist.{$name}")) {
            return $default;
        }

        $value = $default;

        $this->migrator->update("playlist.{$name}", function (mixed $current) use (&$value): mixed {
            $value = $current;

            return $current;
        });

        $this->migrator->delete("playlist.{$name}");

        return $value;
    }
};
