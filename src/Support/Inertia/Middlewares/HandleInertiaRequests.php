<?php

declare(strict_types=1);

namespace Support\Inertia\Middlewares;

use App\Api\Users\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Traits\Conditionable;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    use Conditionable;

    /**
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'layouts/app';

    /**
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'app' => fn () => config('app.name', 'Laravel'),
            'locale' => fn () => app()->currentLocale(),
            'location' => fn () => $request->url(),
            'query' => fn () => $request->query(),
            'flash' => fn () => $this->when($request->hasSession(), fn () => $request->session()->get('laravel_flash_message')),
            'auth.user' => fn () => $this->when($request->user(), fn () => UserResource::make($request->user()->loadMissing('permissions', 'roles'))),
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
