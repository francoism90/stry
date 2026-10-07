<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    /**
     * Reels can be encoded on the CPU, and go back to H.264 by default, since some GPUs (AMD with
     * VAAPI) write corrupted HEVC.
     */
    public function up(): void
    {
        if (! $this->migrator->exists('reels.hardware')) {
            $this->migrator->add('reels.hardware', true);
        }

        if ($this->migrator->exists('reels.codec')) {
            $this->migrator->update('reels.codec', fn (mixed $codec): mixed => $codec === 'hevc' ? 'h264' : $codec);
        }
    }
};
