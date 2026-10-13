<?php

declare(strict_types=1);

namespace Foundation\Providers;

use Domain\Users\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->configureGates();
    }

    protected function configureGates(): void
    {
        Gate::define('manage-application-settings', fn (User $user): bool => $user->isAdmin());
    }
}
