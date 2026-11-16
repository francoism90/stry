<?php

use Support\Octane\CaddySites;

it('renders a site block per service', function () {
    expect(CaddySites::render([
        's3.example.com' => 'systemd-stry-rustfs:9000',
        'ws.example.com' => 'systemd-stry-reverb:6001',
    ], 8000))->toBe("http://s3.example.com:8000 {\n\treverse_proxy systemd-stry-rustfs:9000\n}\n\nhttp://ws.example.com:8000 {\n\treverse_proxy systemd-stry-reverb:6001\n}");
});

it('skips services without a hostname or upstream', function () {
    expect(CaddySites::render([
        '' => 'systemd-stry-mailpit:8025',
        's3.example.com' => CaddySites::hostPortFromUrl('https://s3.amazonaws.com'),
    ], 8000))->toBe('');
});

it('reads the host and port of a url', function () {
    expect(CaddySites::hostFromUrl('https://s3.example.com/bucket'))->toBe('s3.example.com')
        ->and(CaddySites::hostPortFromUrl('http://systemd-stry-rustfs:9000'))->toBe('systemd-stry-rustfs:9000')
        ->and(CaddySites::hostPort('systemd-stry-reverb', null))->toBe('');
});
