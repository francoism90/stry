<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    /**
     * Reel options get their own group. Values saved under processing by earlier builds are moved over.
     */
    public function up(): void
    {
        $defaults = [
            'enabled' => ['processing.create_reels', false],
            'width' => ['processing.reel_width', 1080],
            'height' => ['processing.reel_height', 1920],
            'fps' => ['processing.reel_fps', 30],
            'cuts' => [null, 8],
            'cut_duration' => [null, 4.0],
            'duration' => [null, 32],
            'scene_threshold' => [null, 0.3],
        ];

        foreach ($defaults as $name => [$previous, $default]) {
            $value = $previous !== null ? $this->pull($previous, $default) : $default;

            if (! $this->migrator->exists("reels.{$name}")) {
                $this->migrator->add("reels.{$name}", $value);
            }
        }
    }

    protected function pull(string $name, mixed $default): mixed
    {
        if (! $this->migrator->exists($name)) {
            return $default;
        }

        $value = $default;

        $this->migrator->update($name, function (mixed $current) use (&$value): mixed {
            $value = $current;

            return $current;
        });

        $this->migrator->delete($name);

        return $value;
    }
};
