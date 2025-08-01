<?php

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
            'name' => fake()->sentence(),
            'type' => fake()->randomElement(TagType::cases()),
        ];
    }
}
