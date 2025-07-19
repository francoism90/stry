<?php

namespace Database\Factories;

use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;

class VideoFactory extends Factory
{
    protected $model = Video::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->sentence(),
            'content' => fake()->paragraph(),
            'summary' => fake()->sentence(),
            'published_at' => now(),
        ];
    }
}
