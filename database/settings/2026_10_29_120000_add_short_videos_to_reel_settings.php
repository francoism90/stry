<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        if (! $this->migrator->exists('reels.short_videos')) {
            $this->migrator->add('reels.short_videos', false);
        }

        if (! $this->migrator->exists('reels.short_duration')) {
            $this->migrator->add('reels.short_duration', 60);
        }
    }
};
