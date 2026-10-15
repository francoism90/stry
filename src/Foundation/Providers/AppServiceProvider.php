<?php

declare(strict_types=1);

namespace Foundation\Providers;

use Domain\Profiles\Support\CurrentProfileContext;
use Foundation\Settings\StorageSettings;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Spatie\LaravelSettings\Events\SettingsSaved;
use Support\Filesystem\FilesystemManager;
use Support\Filesystem\RegisterStorageDisks;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerFilesystem();
        $this->registerTelescope();
        $this->registerProfileContext();
    }

    public function boot(): void
    {
        $this->bootStorageDisks();
    }

    protected function registerFilesystem(): void
    {
        $this->app->singleton('filesystem', fn ($app) => new FilesystemManager($app));
    }

    protected function registerTelescope(): void
    {
        if ((bool) config('telescope.enabled') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    protected function registerProfileContext(): void
    {
        $this->app->scoped(CurrentProfileContext::class, fn (): CurrentProfileContext => new CurrentProfileContext);
    }

    /**
     * Disks from the storage settings are registered at boot, and again when they're saved, after
     * migrating and for each queued job, so long-running workers pick up changes.
     */
    protected function bootStorageDisks(): void
    {
        $register = fn () => $this->app->make(RegisterStorageDisks::class)->handle();

        $register();

        Event::listen(MigrationsEnded::class, $register);
        Event::listen(JobProcessing::class, $register);
        Event::listen(SettingsSaved::class, function (SettingsSaved $event) use ($register): void {
            if ($event->settings instanceof StorageSettings) {
                $register();
            }
        });
    }
}
