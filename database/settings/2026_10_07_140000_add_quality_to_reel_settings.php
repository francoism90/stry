<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        if (! $this->migrator->exists('reels.quality')) {
            $this->migrator->add('reels.quality', 26);
        }
    }
};
