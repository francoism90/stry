<?php

use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->inGroup('chapters', function (SettingsBlueprint $blueprint): void {
            $blueprint->add('patterns', [
                'intro' => '/\b(intro(duction)?|leader|opening)\b/i',
                'recap' => '/\b(recap|previously|catch[- ]?up)\b/i',
                'credits' => '/\b(credits?|end\s?card|outro)\b/i',
            ]);
            $blueprint->add('default_type', 'scene');
        });
    }
};
