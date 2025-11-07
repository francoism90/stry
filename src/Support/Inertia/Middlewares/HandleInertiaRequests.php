<?php

declare(strict_types=1);

namespace Support\Inertia\Middlewares;

use App\Web\Users\Responses\AuthenticatedProperty;
use Illuminate\Http\Request;
use Illuminate\Support\Traits\Conditionable;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    use Conditionable;

    /**
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'app' => fn () => config('app.name', 'Laravel'),
            'locale' => fn () => $request->getLocale(),
            'auth' => fn () => new AuthenticatedProperty($request->user()),
        ]);
    }

    /**
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }
}
