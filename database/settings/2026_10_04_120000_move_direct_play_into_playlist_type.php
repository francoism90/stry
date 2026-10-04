<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        if (! $this->migrator->exists('playlist.direct_play')) {
            return;
        }

        $directPlay = false;

        $this->migrator->update('playlist.direct_play', function (mixed $value) use (&$directPlay): mixed {
            $directPlay = (bool) $value;

            return $value;
        });

        if ($directPlay) {
            $this->migrator->update('playlist.type', fn (): string => 'direct');
        }

        $this->migrator->delete('playlist.direct_play');
    }
};
