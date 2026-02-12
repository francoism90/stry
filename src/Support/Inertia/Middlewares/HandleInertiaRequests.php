<?php

declare(strict_types=1);

namespace Support\Inertia\Middlewares;

use App\Api\Users\Resources\UserResource;
use Domain\Users\Models\User;
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
            'nonce' => Inertia::once(fn (): string =>  app('csp-nonce')),
            'locale' => Inertia::once(fn (): string => $request->getLocale()),
            'auth' => Inertia::once(fn (): ?UserResource => $this->auth($request->user())),
        ]);
    }

    public function auth(?User $user = null): ?UserResource
    {
        if (! $user) {
            return null;
        }

        return UserResource::make($user
            ->loadMissing('permissions', 'roles')
            ->append('name', 'avatar'),
        );
    }

    /**
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }
}
