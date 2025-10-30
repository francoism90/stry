<?php

declare(strict_types=1);

namespace App\Web\Dashboard\Controllers;

use App\Api\Videos\Requests\VideoIndexRequest;
use App\Api\Videos\Resources\VideoResource;
use App\Web\Videos\Responses\VideoCollectionProperty;
use App\Web\Videos\Responses\VideoFilterProperty;
use App\Web\Videos\Responses\VideoSectionCollection;
use Domain\Videos\Enums\VideoOrder;
use Domain\Videos\Models\Video;
use Domain\Videos\Scopes\VideoSearchScope;
use Foundation\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('verified'),
        ];
    }

    public function __invoke(VideoIndexRequest $request, VideoCollectionProperty $collection): Response
    {
        Gate::authorize('viewAny', Video::class);

        return Inertia::render('Dashboard/DashboardIndex', [
            'orders' => fn () => VideoOrder::options(),
            $collection
        ]);
    }
}
