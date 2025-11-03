<?php

declare(strict_types=1);

namespace Database\Factories;

use Domain\Playlists\Models\Playlist;
use Domain\Playlists\States\Failed;
use Domain\Playlists\States\Pending;
use Domain\Playlists\States\Verified;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlaylistFactory extends Factory
{
    protected $model = Playlist::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'playlistable_type' => Video::class,
            'playlistable_id' => Video::factory(),
            'disk' => 'segments',
            'file_name' => fake()->uuid().'.m3u8',
            'secret_disk' => 'secrets',
            'state' => Pending::class,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => Verified::class,
            'transcoded_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => Failed::class,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay(),
        ]);
    }

    public function withProgress(float $percentage = 50): static
    {
        return $this->state(fn (array $attributes) => [
            'progress' => [
                'percentage' => $percentage,
                'current' => (int) ($percentage / 10),
                'total' => 10,
            ],
        ]);
    }
}
