<?php

declare(strict_types=1);

namespace Foundation\Providers;

use Domain\Users\Models\User;
use Domain\Users\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Password;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
    ];

    public function boot(): void
    {
        $this->configurePasswords();
        $this->configurePolicyAutoDiscovery();
    }

    protected function configurePasswords(): void
    {
        Password::defaults(fn (): ?Password => app()->isProduction() ? Password::min(10)->max(40)->uncompromised() : null);
    }

    protected function configurePolicyAutoDiscovery(): void
    {
        Gate::guessPolicyNamesUsing(function (string $modelClass) {
            $className = class_basename($modelClass);

            $namespace = str($className)->pluralStudly();

            return str("{$className}Policy")
                ->prepend("Domain\\{$namespace}\\Policies\\")
                ->value();
        });
    }
}
