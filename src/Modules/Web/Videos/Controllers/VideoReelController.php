<?php

declare(strict_types=1);

namespace Modules\Web\Videos\Controllers;

use Domain\Profiles\Models\Profile;
use Domain\Videos\Actions\GetReelShuffleSeed;
use Domain\Videos\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Api\Videos\Resources\VideoReelResource;

class VideoReelController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
        ];
    }

    public function index(Request $request): Response
    {
        // A new shuffle on every visit, kept while scrolling to the next pages
        $seed = app(GetReelShuffleSeed::class)->handle(
            session: $request->session(),
            renew: $request->integer('page', 1) <= 1,
        );

        $reels = Video::query()
            ->verified()
            ->forProfile(Profile::current())
            ->whereHas('media', fn ($query) => $query->where('collection_name', 'reels'))
            ->with(['media'])
            ->shuffled($seed)
            ->simplePaginate(8);

        return Inertia::render('Videos/VideoReels', [
            'items' => Inertia::scroll(fn () => VideoReelResource::collection($reels)),
        ]);
    }
}
