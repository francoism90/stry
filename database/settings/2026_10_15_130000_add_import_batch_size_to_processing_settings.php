<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    /**
     * The import batch size moves from VIDEO_IMPORT_BATCH_SIZE into the processing settings, where
     * admins change it under Admin → Processing.
     */
    public function up(): void
    {
        if (! $this->migrator->exists('processing.import_batch_size')) {
            $this->migrator->add('processing.import_batch_size', 10);
        }
    }
};
