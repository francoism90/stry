<?php

declare(strict_types=1);

namespace Foundation\Providers;

use Domain\Users\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    public function register(): void
    {
        Telescope::night();

        $this->hideSensitiveRequestDetails();

        Telescope::filter(function (IncomingEntry $entry) {
            if ($this->app->environment('local')) {
                return true;
            }

            return $entry->isReportableException() ||
                $entry->isFailedJob() ||
                $entry->isScheduledTask() ||
                $entry->isSlowQuery() ||
                $entry->hasMonitoredTag();
        });

        Telescope::filterBatch(function (Collection $entries) {
            if ($this->app->environment('local')) {
                return true;
            }

            return $entries->contains(fn (IncomingEntry $entry) => $entry->isReportableException() ||
                $entry->isFailedJob() ||
                $entry->isScheduledTask() ||
                $entry->isSlowQuery() ||
                $entry->hasMonitoredTag()
            );
        });
    }

    protected function hideSensitiveRequestDetails(): void
    {
        Telescope::hideRequestParameters([
            '_token',
        ]);

        Telescope::hideRequestHeaders([
            'authorization',
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }

    protected function gate(): void
    {
        Gate::define('viewTelescope', fn (User $user) => $user->hasRole('super-admin'));
    }
}
