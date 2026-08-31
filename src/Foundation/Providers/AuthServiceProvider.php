<?php

declare(strict_types=1);

namespace Foundation\Providers;

use Domain\Users\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->configurePasswords();
        $this->configurePolicyAutoDiscovery();
        $this->configureGates();
    }

    protected function configurePasswords(): void
    {
        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(10)->max(24)->uncompromised()
            : null
        );
    }

    protected function configurePolicyAutoDiscovery(): void
    {
        Gate::guessPolicyNamesUsing(function (string $modelClass) {
            // Get the base name of the model class (e.g., "User" from "App\Models\User")
            $className = class_basename($modelClass);

            // Pluralize the class name to determine the namespace (e.g., "Users" for "User")
            $namespace = Str::pluralStudly($className);

            return Str::of("{$className}Policy")
                ->prepend("Domain\\{$namespace}\\Policies\\")
                ->value();
        });
    }

    protected function configureGates(): void
    {
        Gate::define('manage-application-settings', fn (User $user): bool => $user->isAdmin());
    }
}
