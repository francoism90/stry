<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        if (! $this->migrator->exists('playback.renditions')) {
            $this->migrator->add('playback.renditions', []);
        }
    }
};
