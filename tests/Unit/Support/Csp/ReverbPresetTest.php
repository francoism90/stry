<?php

use Foxws\Essentials\Csp\Presets\ReverbPreset;
use Spatie\Csp\Policy;

it('allows websocket connections to reverb', function (string $scheme, int $port, string $origin) {
    config([
        'reverb.apps.apps.0.options.wsHost' => 'ws.example.test',
        'reverb.apps.apps.0.options.wsPort' => $port,
        'reverb.apps.apps.0.options.wsScheme' => $scheme,
    ]);

    $policy = new Policy;
    (new ReverbPreset)->configure($policy);

    expect($policy->getContents())->toBe("connect-src {$origin}");
})->with([
    'secure on the default port' => ['https', 443, 'wss://ws.example.test'],
    'plain on its own port' => ['http', 8080, 'ws://ws.example.test:8080'],
]);

it('adds no reverb origin when no host is configured', function () {
    config(['reverb.apps.apps.0.options' => ['wsHost' => '', 'host' => null]]);

    $policy = new Policy;
    (new ReverbPreset)->configure($policy);

    expect($policy->getContents())->toBe('');
});
