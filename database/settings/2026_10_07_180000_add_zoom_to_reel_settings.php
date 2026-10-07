<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    /**
     * Cropping zooms by a setting now, which also covers showing the whole picture on a blurred
     * background, so the separate blur fit makes way for crop.
     */
    public function up(): void
    {
        if (! $this->migrator->exists('reels.zoom')) {
            $this->migrator->add('reels.zoom', 50);
        }

        if ($this->migrator->exists('reels.fit')) {
            $this->migrator->update('reels.fit', fn (mixed $fit): mixed => $fit === 'blur' ? 'crop' : $fit);
        }
    }
};
