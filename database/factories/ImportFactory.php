<?php

namespace Database\Factories;

use Domain\Imports\Models\Import;
use Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ImportFactory extends Factory
{
    protected $model = Import::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
        ];
    }
}
