<?php

declare(strict_types=1);

namespace Support\Inertia\Middlewares;

use App\Web\Users\Responses\UserResourceProperty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
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
        return array_merge(parent::shareOnce($request), [
            'app' => fn (): string => Config::string('app.name', 'Laravel'),
            'nonce' => fn (): string => app('csp-nonce'),
            'locale' => fn (): string => $request->getLocale(),
            'search' => fn (): ?string => $request->session()->cache()->get('search'),
            'auth' => fn (): ?UserResourceProperty => new UserResourceProperty(
                user: $request->user() ?? null,
                appends: ['name', 'email', 'avatar', 'settings']
            ),
            'echo' => fn (): array => [
                'key' => Config::string('reverb.apps.apps.0.options.wsKey', ''),
                'host' => Config::string('reverb.apps.apps.0.options.wsHost', 'localhost'),
                'port' => Config::integer('reverb.apps.apps.0.options.wsPort', 6001),
                'scheme' => Config::string('reverb.apps.apps.0.options.wsScheme', 'http'),
            ],
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
