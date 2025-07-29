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
            'primary' => 'alert-primary',
            'secondary' => 'alert-secondary',
            'success' => 'alert-success',
            'info' => 'alert-info',
            'warning' => 'alert-warning',
            'error' => 'alert-error',
            'neutral' => 'alert-neutral',
        ]);
    }
}
