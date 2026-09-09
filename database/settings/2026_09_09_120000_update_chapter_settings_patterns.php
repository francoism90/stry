<?php

use Spatie\LaravelSettings\Migrations\SettingsBlueprint;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->inGroup('chapters', function (SettingsBlueprint $blueprint): void {
            $blueprint->update('patterns', fn ($patterns): array => array_merge((array) $patterns, [
                'intro' => '/\b(intro(duction)?|leader|opening|op|teaser)\b/i',
                'recap' => '/\b(recap|previously|catch[- ]?up|preview|story so far)\b/i',
                'credits' => '/\b(credits?|end\s?card|outro|ed|closing)\b/i',
                'sponsor' => '/\b(sponsor(ed|s)?|advertisement|promo(tion)?)\b/i',
                'filler' => '/\bfiller\b/i',
                'interaction_reminder' => '/\b(like\s?(and|&)?\s?subscribe|subscribe reminder|interaction reminder)\b/i',
            ]));
        });
    }
};
