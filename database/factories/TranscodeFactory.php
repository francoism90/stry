<?php

declare(strict_types=1);

namespace Database\Factories;

use Domain\Transcodes\Enums\TranscodeEncoder;
use Domain\Transcodes\Models\Transcode;
use Domain\Transcodes\States\Completed;
use Domain\Transcodes\States\Failed;
use Domain\Transcodes\States\Imported;
use Domain\Transcodes\States\Pending;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transcode>
 */
class TranscodeFactory extends Factory
{
    protected $model = Transcode::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'transcodable_type' => Video::class,
            'transcodable_id' => Video::factory(),
            'disk' => 'transcodes',
            'file_name' => null,
            'encoder' => TranscodeEncoder::AV1,
            'state' => Pending::class,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_name' => 'video_av1.mp4',
            'state' => Completed::class,
            'transcoded_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => Failed::class,
        ]);
    }

    public function imported(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => Imported::class,
            'transcoded_at' => now()->subDays(8),
            'created_at' => now()->subDays(8),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => Failed::class,
            'created_at' => now()->subDays(8),
        ]);
    }
}
