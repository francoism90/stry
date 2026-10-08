<?php

declare(strict_types=1);

namespace Database\Factories;

use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Domain\Videos\States\Pending;
use Domain\Videos\States\Verified;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Video>
 */
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

    /**
     * Give the video a clip whose stream lasts the given number of seconds.
     */
    public function withDuration(float $seconds): static
    {
        return $this->afterCreating(function (Video $video) use ($seconds): void {
            $video->media()->create([
                'collection_name' => 'clips',
                'name' => 'clip',
                'file_name' => 'clip.mp4',
                'mime_type' => 'video/mp4',
                'disk' => 'media',
                'size' => 1,
                'manipulations' => [],
                'custom_properties' => [
                    'streams' => [
                        ['codec_type' => 'video', 'duration' => $seconds],
                    ],
                ],
                'generated_conversions' => [],
                'responsive_images' => [],
            ]);

            // Indexing the new video already worked out (and cached) its duration without the clip
            $video->refresh();
        });
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => Pending::class,
        ]);
    }
}
