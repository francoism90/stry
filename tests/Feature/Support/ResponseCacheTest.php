<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;

it('keeps other cached values when the response cache is cleared', function () {
    config(['responsecache.cache.store' => config('cache.default')]);

    Cache::forever('kept', 'value');

    $this->artisan('responsecache:clear')->assertSuccessful();

    expect(Cache::get('kept'))->toBe('value');
});
