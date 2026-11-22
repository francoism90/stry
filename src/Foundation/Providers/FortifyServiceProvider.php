<?php

declare(strict_types=1);

namespace Foundation\Providers;

use Domain\Users\Actions\CreateNewUser;
use Domain\Users\Actions\ResetUserPassword;
use Domain\Users\Actions\UpdateUserPassword;
use Domain\Users\Actions\UpdateUserProfileInformation;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Fortify\Contracts\PasswordUpdateResponse as PasswordUpdateResponseContract;
use Laravel\Fortify\Contracts\ProfileInformationUpdatedResponse as ProfileInformationUpdatedResponseContract;
use Laravel\Fortify\Fortify;
use Modules\Web\Account\Responses\PasswordUpdateResponse;
use Modules\Web\Account\Responses\ProfileInformationUpdatedResponse;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PasswordUpdateResponseContract::class, PasswordUpdateResponse::class);
        $this->app->singleton(ProfileInformationUpdatedResponseContract::class, ProfileInformationUpdatedResponse::class);
    }

    public function boot(): void
    {
        $this->configureActions();
        $this->configureRateLimits();
        $this->configureResetLinks();
        $this->configureViews();
    }

    protected function configureActions(): static
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        return $this;
    }

    protected function configureRateLimits(): static
    {
        RateLimiter::for('login', function (Request $request) {
            $account = Str::transliterate(Str::lower($request->string(Fortify::username())->value()));

            // The address can be spoofed behind the on-demand socket, so the account has a limit of its own
            return [
                Limit::perMinute(5)->by("address:{$account}|{$request->ip()}"),
                Limit::perMinute(20)->by("account:{$account}"),
            ];
        });

        // The routes that check the password of the signed in user
        RateLimiter::for('password-checks', function (Request $request) {
            $checksPassword = $request->routeIs('password.confirm.store', 'user-password.update')
                || ($request->routeIs('user-profile-information.update', 'users.update') && $request->filled('current_password'));

            if (! $checksPassword) {
                return Limit::none();
            }

            return Limit::perMinute(6)->by($request->user()?->getRouteKey() ?? $request->ip());
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('passkeys', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->getAuthIdentifier() ?? $request->ip());
        });

        return $this;
    }

    /**
     * Reset links are mailed, so their host is APP_URL's and never the request's, which anyone can choose.
     */
    protected function configureResetLinks(): static
    {
        ResetPassword::createUrlUsing(function (CanResetPassword $user, string $token): string {
            // Not absolute: that takes the host from the request, which anyone can forge
            $path = route('password.reset', [
                'token' => $token,
                'email' => $user->getEmailForPasswordReset(),
            ], absolute: false);

            return rtrim(config()->string('app.url'), '/').$path;
        });

        return $this;
    }

    protected function configureViews(): static
    {
        Fortify::loginView(fn () => Inertia::render('Auth/AuthLogin', [
            'action' => fn () => route('login.store', absolute: false),
            'forgotPasswordUrl' => fn () => route('password.request', absolute: false),
            'status' => fn () => session('status'),
        ]));

        Fortify::requestPasswordResetLinkView(fn () => Inertia::render('Auth/AuthForgotPassword', [
            'action' => fn () => route('password.email', absolute: false),
            'loginUrl' => fn () => route('login', absolute: false),
            'status' => fn () => session('status'),
        ]));

        Fortify::resetPasswordView(fn (Request $request) => Inertia::render('Auth/AuthResetPassword', [
            'action' => fn () => route('password.update', absolute: false),
            'token' => fn () => $request->route('token'),
            'email' => fn () => $request->string('email')->value(),
        ]));

        Fortify::verifyEmailView(fn () => Inertia::render('Auth/AuthVerifyEmail', [
            'action' => fn () => route('verification.send', absolute: false),
            'status' => fn () => session('status'),
        ]));

        Fortify::confirmPasswordView(fn () => Inertia::render('Auth/AuthConfirmPassword', [
            'action' => fn () => route('password.confirm.store', absolute: false),
        ]));

        return $this;
    }
}
