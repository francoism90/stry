<?php

use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->inGroup('processing', function (SettingsBlueprint $blueprint): void {
            $blueprint->add('extract_captions', true);
            $blueprint->add('extract_chapters', true);
            $blueprint->add('extract_storyboard', true);
        });
    }
};
