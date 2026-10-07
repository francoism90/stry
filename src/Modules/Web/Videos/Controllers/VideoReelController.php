<?php

declare(strict_types=1);

namespace Modules\Web\Videos\Controllers;

use Domain\Profiles\Models\Profile;
use Domain\Videos\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Api\Videos\Resources\VideoReelResource;

class VideoReelController implements HasMiddleware
{
    /**
     * A prime above any video ID, so ordering by (id × seed) mod it shuffles the reels without
     * database-specific functions.
     */
    protected const int SHUFFLE_MODULUS = 1000003;

    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('verified'),
        ];
    }

    public function index(Request $request): Response
    {
        $seed = $this->seed($request);

        $reels = Video::query()
            ->verified()
            ->forProfile(Profile::current())
            ->whereHas('media', fn ($query) => $query->where('collection_name', 'reels'))
            ->with(['media'])
            ->orderByRaw('(id * ?) % ?', [$seed, self::SHUFFLE_MODULUS])
            ->simplePaginate(8);

        return Inertia::render('Videos/VideoReels', [
            'items' => Inertia::scroll(fn () => VideoReelResource::collection($reels)),
        ]);
    }

    /**
     * The shuffle seed, new on the first page so every visit starts a new order, and kept in the
     * session so the next pages continue it.
     */
    protected function seed(Request $request): int
    {
        if ($request->integer('page', 1) <= 1 || ! $request->session()->has('reels.seed')) {
            $request->session()->put('reels.seed', random_int(1, self::SHUFFLE_MODULUS - 1));
        }

        return (int) $request->session()->get('reels.seed');
    }
}
