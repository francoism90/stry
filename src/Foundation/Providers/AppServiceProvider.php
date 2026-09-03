<?php

declare(strict_types=1);

namespace Foundation\Providers;

use Domain\Profiles\Support\CurrentProfileContext;
use Illuminate\Support\ServiceProvider;
use Support\Filesystem\FilesystemManager;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerFilesystem();
        $this->registerTelescope();
        $this->registerProfileContext();
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
}
