<?php

declare(strict_types=1);

namespace Database\Factories;

use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Domain\Videos\States\Pending;
use Domain\Videos\States\Verified;
use Illuminate\Database\Eloquent\Factories\Factory;

class VideoFactory extends Factory
{
    protected $model = Video::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => ['en' => fake()->sentence()],
            'content' => ['en' => fake()->paragraph()],
            'summary' => ['en' => fake()->paragraph()],
            'published_at' => now(),
            'state' => Verified::class,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => Verified::class,
            'published_at' => now(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => Pending::class,
        ]);
    }
}
