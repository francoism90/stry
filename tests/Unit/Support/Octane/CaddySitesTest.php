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
