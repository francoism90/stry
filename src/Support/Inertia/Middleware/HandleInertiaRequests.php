<?php

declare(strict_types=1);

namespace Support\Inertia\Middleware;

use Domain\Shared\Enums\Language;
use Domain\Shared\Enums\Locale;
use Domain\Tags\Enums\TagType;
use Domain\Users\Models\User;
use Foundation\Settings\GeneralSettings;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Inertia\Inertia;
use Inertia\Middleware;
use Modules\Web\Groups\Responses\GroupCollectionsProperty;
use Modules\Web\Settings\Responses\ApplicationNoticesProperty;
use Modules\Web\Users\Responses\UserResourceProperty;
use Spatie\LaravelOptions\Options;

class HandleInertiaRequests extends Middleware
{
    /**
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        /** @var User|null $user */
        $user = $request->user();

        return array_merge(parent::share($request), [
            'nonce' => fn (): string => app('csp-nonce'),
            'locale' => fn (): string => $request->getLocale(),
            'auth' => fn (): UserResourceProperty => new UserResourceProperty(
                user: $user,
                appends: ['name', 'email', 'avatar', 'settings']
            ),
            'notices' => fn (): ApplicationNoticesProperty => new ApplicationNoticesProperty($user),
            'collections' => $this->collections($user),
            'unread' => fn (): int => $user?->unreadNotifications()->count() ?? 0,
        ]);
    }

    /**
     * Reloads the sidebar collections whenever one of the user's groups changes.
     *
     * @see https://inertiajs.com/docs/v3/data-props/live-props
     */
    protected function collections(?User $user): mixed
    {
        $collections = fn (): GroupCollectionsProperty => new GroupCollectionsProperty($user);

        if (! $user) {
            return $collections;
        }

        return Inertia::live(
            $collections,
            on: ['group.created', 'group.updated', 'group.trashed'],
            channel: new PrivateChannel($user->broadcastChannel()),
        );
    }

    /**
     * @see https://inertiajs.com/docs/v3/data-props/shared-data#sharing-once-props
     *
     * @return array<string, mixed>
     */
    public function shareOnce(Request $request): array
    {
        return array_merge(parent::shareOnce($request), [
            'app' => fn (): string => app(GeneralSettings::class)->site_name,
            'locales' => fn (): Options => Options::forEnum(Locale::class),
            'languages' => fn (): Options => Options::forEnum(Language::class),
            'tags' => fn (): Options => Options::forEnum(TagType::class),
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
