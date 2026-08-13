<?php

use Support\Octane\CaddySites;

it('renders a reverse_proxy block per configured site', function () {
    $config = CaddySites::render([
        'stry-s3.domain.myds.me' => 'systemd-stry-rustfs:9000',
        'stry-ws.domain.myds.me' => 'systemd-stry-reverb:6001',
    ]);

    expect($config)
        ->toContain("stry-s3.domain.myds.me {\n\treverse_proxy systemd-stry-rustfs:9000\n}")
        ->toContain("stry-ws.domain.myds.me {\n\treverse_proxy systemd-stry-reverb:6001\n}");
});

it('skips sites with an empty host or upstream', function () {
    $config = CaddySites::render([
        '' => 'systemd-stry-rustfs:9000',
        'stry-mailpit.domain.myds.me' => '',
    ]);

    expect($config)->toBe('');
});

it('extracts the host from a url', function () {
    expect(CaddySites::hostFromUrl('https://stry-s3.domain.myds.me'))
        ->toBe('stry-s3.domain.myds.me');

    expect(CaddySites::hostFromUrl(''))->toBe('');
});
