<?php

declare(strict_types=1);

namespace Foundation\Providers;

use Illuminate\Foundation\Events\DiscoverEvents;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Str;
use SplFileInfo;

class EventServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->configureEventAutoDiscovery();
    }

    protected function configureEventAutoDiscovery(): void
    {
        DiscoverEvents::guessClassNamesUsing(function (SplFileInfo $file): string {
            $class = trim(Str::replaceFirst(base_path('src'), '', $file->getRealPath()), DIRECTORY_SEPARATOR);

            return ucfirst(Str::camel(str_replace(
                [DIRECTORY_SEPARATOR, ucfirst(basename(app()->path())).'\\'],
                ['\\', app()->getNamespace()],
                ucfirst(Str::replaceLast('.php', '', $class))
            )));
        });
    }
}
