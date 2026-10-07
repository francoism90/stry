<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    /**
     * The blur fit zooms by a setting now, and crop, which fills the full height, is called fill.
     */
    public function up(): void
    {
        if (! $this->migrator->exists('reels.zoom')) {
            $this->migrator->add('reels.zoom', 50);
        }

        if ($this->migrator->exists('reels.fit')) {
            $this->migrator->update('reels.fit', fn (mixed $fit): mixed => $fit === 'crop' ? 'fill' : $fit);
        }
    }
};
