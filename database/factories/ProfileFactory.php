<?php

declare(strict_types=1);

namespace Database\Factories;

use Domain\Profiles\Models\Profile;
use Domain\Profiles\States\Enabled;
use Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileFactory extends Factory
{
    protected $model = Profile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->firstName(),
            'avatar' => null,
            'is_kids' => false,
            'is_primary' => false,
            'state' => Enabled::class,
            'settings' => [],
        ];
    }

    public function kids(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_kids' => true,
        ]);
    }

    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => true,
        ]);
    }
}
