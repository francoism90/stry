<?php

use Illuminate\Support\Facades\Storage;
use Support\Filesystem\S3TemporaryUrlAdapter;

it('resolves s3 disks as S3TemporaryUrlAdapter', function () {
    expect(Storage::disk('conversions'))->toBeInstanceOf(S3TemporaryUrlAdapter::class);
    expect(Storage::disk('s3'))->toBeInstanceOf(S3TemporaryUrlAdapter::class);
    expect(Storage::disk('secrets'))->toBeInstanceOf(S3TemporaryUrlAdapter::class);
});

it('generates temporary urls signed with the public endpoint', function () {
    $url = Storage::disk('conversions')->temporaryUrl('test/file.avif', now()->addHour());

    $host = parse_url($url, PHP_URL_HOST);

    expect($host)->toBe('s3.spiky.test');
});

it('falls back to default behaviour when endpoint matches temporary_url', function () {
    $disk = Storage::build([
        'driver' => 's3',
        'key' => 'key',
        'secret' => 'secret',
        'region' => 'us-east-1',
        'bucket' => 'test',
        'endpoint' => 'https://s3.example.com',
        'temporary_url' => 'https://s3.example.com',
        'use_path_style_endpoint' => true,
    ]);

    expect($disk)->toBeInstanceOf(S3TemporaryUrlAdapter::class);

    $url = $disk->temporaryUrl('test/file.avif', now()->addHour());

    expect(parse_url($url, PHP_URL_HOST))->toBe('s3.example.com');
});
