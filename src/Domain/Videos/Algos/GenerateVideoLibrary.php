<?php

declare(strict_types=1);

namespace Domain\Videos\Algos;

use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Illuminate\Pagination\CursorPaginator;

class GenerateVideoLibrary
{
    public function handle(?User $user = null, ?string $type = null, ?int $limit = null): CursorPaginator
    {
        return Video::query()
            ->orderByDesc('created_at')
            ->cursorPaginate($limit ?? 12);
    }
}
