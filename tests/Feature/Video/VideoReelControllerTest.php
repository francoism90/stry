<?php

declare(strict_types=1);

use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Inertia\Testing\AssertableInertia;
use Modules\Web\Videos\Controllers\VideoReelController;

function addFeedReel(Video $video): void
{
    $video->media()->create([
        'collection_name' => 'reels',
        'name' => 'reel',
        'file_name' => 'reel.mp4',
        'mime_type' => 'video/mp4',
        'disk' => 'media',
        'size' => 1,
        'manipulations' => [],
        'custom_properties' => [],
        'generated_conversions' => [],
        'responsive_images' => [],
    ]);
}

it('lists the reels of valid videos', function () {
    addFeedReel($withReel = Video::factory()->create());
    addFeedReel(Video::factory()->pending()->create());
    Video::factory()->create();

    $response = $this->actingAs(User::factory()->create())->get(action([VideoReelController::class, 'index']));

    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Videos/VideoReels')
        ->has('items.data', 1)
        ->where('items.data.0.id', $withReel->getRouteKey())
        ->has('items.data.0.reel_url'));
});

it('keeps the shuffled order across pages', function () {
    Video::factory()->count(10)->create()->each(fn (Video $video) => addFeedReel($video));
    $user = User::factory()->create();

    $first = data_get($this->actingAs($user)->get(action([VideoReelController::class, 'index']))->viewData('page'), 'props.items.data.*.id');
    $second = data_get($this->actingAs($user)->get(action([VideoReelController::class, 'index'], ['page' => 2]))->viewData('page'), 'props.items.data.*.id');

    expect([...$first, ...$second])->toHaveCount(10)->toEqualCanonicalizing(Video::all()->map->getRouteKey()->all());
});

it('redirects guests to the login page', function () {
    $this->get(action([VideoReelController::class, 'index']))->assertRedirect(route('login'));
});
