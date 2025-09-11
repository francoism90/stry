<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Groups\Enums\GroupType;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Support\Facades\DB;

class MarkVideoAsViewed
{
    public function handle(Video $video, User $user, ?array $attributes = null): Video
    {
        return DB::transaction(function () use ($video, $user, $attributes) {
            // Ensure the user has a viewed group
            $group = $user->findOrCreateGroup(GroupType::Viewed);

            // Update with the video attributes
            $video->syncGroup($group, $attributes);

            return $video;
        });
    }
}
