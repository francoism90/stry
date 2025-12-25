<?php

declare(strict_types=1);

namespace Database\Seeders;

use Domain\Tags\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        Tag::factory()->count(24)->create();
    }
}
