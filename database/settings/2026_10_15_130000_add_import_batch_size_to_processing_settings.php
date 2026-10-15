<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    /**
     * The import batch size moves from VIDEO_IMPORT_BATCH_SIZE into the processing settings, keeping a
     * value that was set there.
     */
    public function up(): void
    {
        if (! $this->migrator->exists('processing.import_batch_size')) {
            $this->migrator->add('processing.import_batch_size', max(1, (int) env('VIDEO_IMPORT_BATCH_SIZE', 10)));
        }
    }
};
