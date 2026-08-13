<?php

use Support\Octane\CaddySites;

it('renders a reverse_proxy block per configured site, pinned to http and the given port', function () {
    $config = CaddySites::render([
        'stry-s3.domain.myds.me' => 'systemd-stry-rustfs:9000',
        'stry-ws.domain.myds.me' => 'systemd-stry-reverb:6001',
    ], 8000);

    expect($config)
        ->toContain("http://stry-s3.domain.myds.me:8000 {\n\treverse_proxy systemd-stry-rustfs:9000\n}")
        ->toContain("http://stry-ws.domain.myds.me:8000 {\n\treverse_proxy systemd-stry-reverb:6001\n}");
});

it('skips sites with an empty host or upstream', function () {
    $config = CaddySites::render([
        '' => 'systemd-stry-rustfs:9000',
        'stry-mailpit.domain.myds.me' => '',
    ], 8000);

    expect($config)->toBe('');
});

it('extracts the host from a url', function () {
    expect(CaddySites::hostFromUrl('https://stry-s3.domain.myds.me'))
        ->toBe('stry-s3.domain.myds.me');

    expect(CaddySites::hostFromUrl(''))->toBe('');
});

it('extracts host:port from a url', function () {
    expect(CaddySites::hostPortFromUrl('http://systemd-stry-rustfs:9000'))
        ->toBe('systemd-stry-rustfs:9000');

    expect(CaddySites::hostPortFromUrl(''))->toBe('');

    // Real AWS S3 has no explicit port -- not something to reverse proxy.
    expect(CaddySites::hostPortFromUrl('https://s3.amazonaws.com'))->toBe('');
});

it('builds host:port, or empty if either half is missing', function () {
    expect(CaddySites::hostPort('systemd-stry-reverb', 6001))
        ->toBe('systemd-stry-reverb:6001');

    expect(CaddySites::hostPort(null, 6001))->toBe('');
    expect(CaddySites::hostPort('systemd-stry-reverb', null))->toBe('');
    expect(CaddySites::hostPort('', 6001))->toBe('');
});
