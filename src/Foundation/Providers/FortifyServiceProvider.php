<?php

declare(strict_types=1);

namespace Foundation\Providers;

use Domain\Users\Actions\CreateNewUser;
use Domain\Users\Actions\ResetUserPassword;
use Domain\Users\Actions\UpdateUserPassword;
use Domain\Users\Actions\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
    }

    protected function configureActions(): static
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        return $this;
    }

    protected function configureViews(): static
    {
        Fortify::loginView(fn () => Inertia::render('Auth/AuthLogin', [
            'action' => fn () => route('login'),
            'status' => fn () => session('status'),
        ]));

        return $this;
    }
}
