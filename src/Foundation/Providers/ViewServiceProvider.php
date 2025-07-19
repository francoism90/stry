<?php

declare(strict_types=1);

namespace Foundation\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Spatie\Flash\Flash;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->configureViteFetching();
        $this->configureSessionFlash();
    }

    protected function configureViteFetching(): void
    {
        Vite::useAggressivePrefetching();
    }

    protected function configureSessionFlash(): void
    {
        Flash::levels([
            'success' => 'alert-success',
            'warning' => 'alert-warning',
            'error' => 'alert-error',
        ]);
    }
}
