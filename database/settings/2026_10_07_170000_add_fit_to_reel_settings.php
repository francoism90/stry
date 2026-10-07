<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        if (! $this->migrator->exists('reels.fit')) {
            $this->migrator->add('reels.fit', 'fill');
        }
    }
};
