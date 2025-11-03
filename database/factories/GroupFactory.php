<?php

declare(strict_types=1);

namespace Database\Factories;

use Domain\Groups\Enums\GroupType;
use Domain\Groups\Models\Group;
use Domain\Groups\States\Pending;
use Domain\Groups\States\Verified;
use Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupFactory extends Factory
{
    protected $model = Group::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'content' => fake()->paragraph(),
            'type' => fake()->randomElement(GroupType::cases()),
            'state' => Verified::class,
            'published_at' => now(),
        ];
    }

    public function favorite(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => GroupType::Favorite,
        ]);
    }

    public function mixer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => GroupType::Mixer,
        ]);
    }

    public function saved(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => GroupType::Saved,
        ]);
    }

    public function viewed(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => GroupType::Viewed,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => Pending::class,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay(),
        ]);
    }
}
