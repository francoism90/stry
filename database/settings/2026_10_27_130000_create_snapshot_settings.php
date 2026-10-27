<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        if (! $this->migrator->exists('snapshots.frequency')) {
            $this->migrator->add('snapshots.frequency', 'never');
        }

        if (! $this->migrator->exists('snapshots.keep')) {
            $this->migrator->add('snapshots.keep', 7);
        }
    }
};
