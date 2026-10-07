<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        foreach (['reel_width' => 1080, 'reel_height' => 1920, 'reel_fps' => 30] as $name => $value) {
            if (! $this->migrator->exists("processing.{$name}")) {
                $this->migrator->add("processing.{$name}", $value);
            }
        }
    }
};
