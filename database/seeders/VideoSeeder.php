<?php

declare(strict_types=1);

namespace Database\Seeders;

use Domain\Videos\Models\Video;
use Illuminate\Database\Seeder;

class VideoSeeder extends Seeder
{
    public function run(): void
    {
        Video::factory()->count(16)->create();
    }
}
