<?php

declare(strict_types=1);

namespace Support\Inertia\Middlewares;

use App\Api\Users\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Inertia\Inertia;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'app' => Inertia::once(fn () => Config::string('app.name', 'Laravel')),
            'locale' => Inertia::once(fn () => $request->getLocale()),
            'auth' => Inertia::once(fn () => UserResource::make($request->user()
                ->loadMissing('permissions', 'roles')
                ->append('name', 'avatar'),
            )),
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
