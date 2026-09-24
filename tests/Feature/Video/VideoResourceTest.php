<?php

declare(strict_types=1);

use Domain\Groups\Enums\GroupType;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Api\Videos\Resources\VideoResource;

function requestFor(?User $user): Request
{
    return tap(Request::create('/'), fn (Request $request) => $request->setUserResolver(fn () => $user));
}

it('resolves group memberships for a collection of videos in a single query', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    [$liked, $saved, $viewed] = Video::factory()->count(3)->create()->all();

    $user->markInGroup($liked, GroupType::Liked);
    $user->markInGroup($saved, GroupType::Saved);
    $otherUser->markInGroup($viewed, GroupType::Viewed);

    $videos = Video::query()->with('tags')->whereKey([$liked->id, $saved->id, $viewed->id])->get();

    DB::enableQueryLog();

    $items = collect(VideoResource::collection($videos)->resolve(requestFor($user)))->keyBy('id');

    $groupQueries = collect(DB::getQueryLog())->filter(fn (array $query) => str_contains($query['query'], 'groupables'));

    expect($groupQueries)->toHaveCount(1)
        ->and($items[$liked->ulid])->toMatchArray(['liked' => true, 'saved' => false, 'viewed' => false])
        ->and($items[$saved->ulid])->toMatchArray(['liked' => false, 'saved' => true, 'viewed' => false])
        ->and($items[$viewed->ulid])->toMatchArray(['liked' => false, 'saved' => false, 'viewed' => false]);
});

it('resolves group memberships for a single video', function () {
    $user = User::factory()->create();
    $video = Video::factory()->create();

    $user->markInGroup($video, GroupType::Viewed);

    $item = VideoResource::make($video)->resolve(requestFor($user));

    expect($item)->toMatchArray(['liked' => false, 'saved' => false, 'viewed' => true]);
});

it('returns no group memberships for guests', function () {
    $video = Video::factory()->create();

    $item = VideoResource::make($video)->resolve(requestFor(null));

    expect($item)->toMatchArray(['liked' => null, 'saved' => null, 'viewed' => null]);
});
