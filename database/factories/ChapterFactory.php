<?php

declare(strict_types=1);

namespace Database\Factories;

use Domain\Chapters\Enums\ChapterType;
use Domain\Chapters\Models\Chapter;
use Domain\Videos\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Chapter>
 */
class ChapterFactory extends Factory
{
    protected $model = Chapter::class;

    public function definition(): array
    {
        $startTime = fake()->randomFloat(2, 0, 300);

        return [
            'video_id' => Video::factory(),
            'type' => ChapterType::Scene,
            'label' => fake()->words(2, true),
            'start_time' => $startTime,
            'end_time' => $startTime + fake()->randomFloat(2, 5, 60),
            'sort' => 0,
        ];
    }

    public function intro(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ChapterType::Intro,
            'label' => 'Intro',
            'start_time' => 0,
            'end_time' => 90,
        ]);
    }

    public function recap(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ChapterType::Recap,
            'label' => 'Recap',
            'start_time' => 90,
            'end_time' => 150,
        ]);
    }

    public function credits(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ChapterType::Credits,
            'label' => 'End Credits',
            'start_time' => 1200,
            'end_time' => 1290,
        ]);
    }
}
