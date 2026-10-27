<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        if (! $this->migrator->exists('processing.warm_streams')) {
            $this->migrator->add('processing.warm_streams', true);
        }
    }
};
