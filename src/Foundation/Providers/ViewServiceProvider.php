<?php

declare(strict_types=1);

namespace Foundation\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->configureVitePrefetching();
    }

    protected function configureVitePrefetching(): void
    {
        Vite::useAggressivePrefetching();
    }
}
