<?php

use Spatie\Csp\Policy;
use Support\Csp\Presets\BasicPreset;

it('scopes connect/img/style/font/media sources to the configured s3 hosts', function () {
    config([
        'app.url' => 'https://app.example.com',
        'filesystems.disks.s3.url' => 'https://s3.example.com',
    ]);

    $policy = new Policy;
    (new BasicPreset)->configure($policy);

    $contents = $policy->getContents();

    expect($contents)
        ->toContain('https://s3.example.com');
});

it('keeps the scheme and port of the s3 disk urls', function () {
    config([
        'app.url' => 'https://app.example.com',
        'filesystems.disks.s3.url' => 'http://rustfs.example.com:9000/bucket',
        'filesystems.disks.s3.temporary_url' => 'https://S3.example.com:443/bucket',
    ]);

    $policy = new Policy;
    (new BasicPreset)->configure($policy);

    $mediaDirective = collect(explode(';', $policy->getContents()))
        ->first(fn (string $directive) => str_starts_with(trim($directive), 'media-src'));

    expect($mediaDirective)
        ->toContain('http://rustfs.example.com:9000')
        ->toContain('https://s3.example.com')
        ->not->toContain('/bucket')
        ->not->toContain(':443');
});

it('allows blob: connections for the video player', function () {
    config([
        'app.url' => 'https://app.example.com',
    ]);

    $policy = new Policy;
    (new BasicPreset)->configure($policy);

    $contents = $policy->getContents();

    $connectDirective = collect(explode(';', $contents))
        ->first(fn (string $directive) => str_starts_with(trim($directive), 'connect-src'));

    expect($connectDirective)->toContain('blob:');
});

it('does not add s3 hosts when they are not configured', function () {
    config([
        'app.url' => 'https://laravel.test',
        'filesystems.disks.s3.url' => null,
    ]);

    $policy = new Policy;
    (new BasicPreset)->configure($policy);

    $contents = $policy->getContents();

    expect($contents)->not->toContain('example.com');
});

it('upgrades insecure requests when the app runs on https', function () {
    config(['app.url' => 'https://app.example.com']);

    $policy = new Policy;
    (new BasicPreset)->configure($policy);

    expect($policy->getContents())->toContain('upgrade-insecure-requests');
});

it('leaves requests alone when the app runs on http', function () {
    config(['app.url' => 'http://localhost:8000']);

    $policy = new Policy;
    (new BasicPreset)->configure($policy);

    expect($policy->getContents())->not->toContain('upgrade-insecure-requests');
});
