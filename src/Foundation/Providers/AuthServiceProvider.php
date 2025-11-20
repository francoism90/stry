<?php

declare(strict_types=1);

namespace Foundation\Providers;

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
    }

    protected function configurePasswords(): void
    {
        Password::defaults(fn (): ?Password => app()->isProduction() ? Password::min(10)->max(32)->uncompromised() : null);
    }

    protected function configurePolicyAutoDiscovery(): void
    {
        Gate::guessPolicyNamesUsing(function (string $modelClass) {
            $className = class_basename($modelClass);

            $namespace = Str::pluralStudly($className);

            return Str::of("{$className}Policy")
                ->prepend("Domain\\{$namespace}\\Policies\\")
                ->value();
        });
    }
}
