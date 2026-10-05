<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Spatie\LaravelSettings\Support\SettingsCacheFactory;

return new class extends SettingsMigration
{
    /**
     * Direct play is the only playback mode now, so the playlist group makes way for a playback
     * group that keeps the subtitle language, whether to encrypt and when to refresh the URLs.
     * Playback settings saved before this ran are kept.
     */
    public function up(): void
    {
        $this->move('text_language', 'text_language', 'en');
        $this->move('encryption', 'encryption', null, fn (mixed $value): bool => $value !== null);
        $this->move('manifest_refresh_before', 'refresh_before', 300);

        foreach (['type', 'disk_name', 'language', 'expires_after', 'manifest_cache_lifetime', 'manifest_url_lifetime', 'media_url_lifetime', 'key_url_lifetime', 'protection_scheme', 'key_rotation', 'key_rotation_duration'] as $name) {
            $this->migrator->deleteIfExists("playlist.{$name}");
        }

        foreach (app(SettingsCacheFactory::class)->all() as $settingsCache) {
            $settingsCache->clear();
        }
    }

    protected function move(string $from, string $to, mixed $default, ?callable $map = null): void
    {
        $value = $this->pull($from, $default);

        if (! $this->migrator->exists("playback.{$to}")) {
            $this->migrator->add("playback.{$to}", $map !== null ? $map($value) : $value);
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
