<?php

declare(strict_types=1);

namespace Support\Inertia\Middlewares;

use App\Web\Users\Responses\UserResourceProperty;
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
            'app' => Inertia::once(fn (): string => Config::string('app.name', 'Laravel')),
            'nonce' => Inertia::once(fn (): string => app('csp-nonce')),
            'locale' => Inertia::once(fn (): string => $request->getLocale()),
            'auth' => Inertia::once(fn (): ?UserResourceProperty => new UserResourceProperty($request->user(), ['name', 'email', 'avatar', 'preferences'])),
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
