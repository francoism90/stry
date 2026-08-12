<?php

use Spatie\Csp\Policy;
use Support\Csp\Presets\BasicPreset;

it('scopes connect/img/style/font/media sources to the configured s3 and reverb hosts', function () {
    config([
        'app.url' => 'https://stry.domain.myds.me',
        'filesystems.disks.s3.url' => 'https://stry-s3.domain.myds.me',
        'reverb.apps.apps.0.options.wsHost' => 'stry-ws.domain.myds.me',
    ]);

    $policy = new Policy;
    (new BasicPreset)->configure($policy);

    $contents = $policy->getContents();

    expect($contents)
        ->toContain('https://stry-s3.domain.myds.me')
        ->toContain('wss://stry-ws.domain.myds.me')
        ->toContain('https://stry-ws.domain.myds.me');
});

it('does not add s3/reverb hosts when they are not configured', function () {
    config([
        'app.url' => 'https://laravel.test',
        'filesystems.disks.s3.url' => null,
        'reverb.apps.apps.0.options.wsHost' => null,
    ]);

    $policy = new Policy;
    (new BasicPreset)->configure($policy);

    $contents = $policy->getContents();

    expect($contents)->not->toContain('domain.myds.me');
});
