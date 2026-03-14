<?php

declare(strict_types=1);

namespace Foundation\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Scout\Builder;

class ScoutServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->configureMacros();
    }

    protected function configureMacros(): void
    {
        Builder::macro('randomOrder', function (?int $seed = null) {
            /** @var Builder $this */
            return $this->orderBy("_rand({$seed})");
        });
    }
}
