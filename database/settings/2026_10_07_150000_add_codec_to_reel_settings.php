<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        if (! $this->migrator->exists('reels.codec')) {
            $this->migrator->add('reels.codec', 'h264');
        }
    }
};
