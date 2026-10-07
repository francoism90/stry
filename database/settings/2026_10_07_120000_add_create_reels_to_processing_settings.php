<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        if (! $this->migrator->exists('processing.create_reels')) {
            $this->migrator->add('processing.create_reels', false);
        }
    }
};
