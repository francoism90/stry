<?php

declare(strict_types=1);

namespace Foundation\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->configureViteFetching();
    }

    protected function configureViteFetching(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
