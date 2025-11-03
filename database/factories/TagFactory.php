<?php

declare(strict_types=1);

namespace Database\Factories;

use Domain\Tags\Enums\TagType;
use Domain\Tags\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

class TagFactory extends Factory
{
    protected $model = Tag::class;

    public function definition(): array
    {
        return [
            'name' => ['en' => fake()->word()],
            'type' => fake()->randomElement(TagType::cases()),
        ];
    }
}
