<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        if (! $this->migrator->exists('playback.completion_threshold')) {
            $this->migrator->add('playback.completion_threshold', 0.95);
        }
    }
};
