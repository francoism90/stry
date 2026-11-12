<?php

declare(strict_types=1);

it('formats a duration', function (float|int $seconds, string $expected) {
    expect(duration($seconds))->toBe($expected);
})->with([
    'seconds' => [5, '0:05'],
    'minutes' => [65.7, '1:05'],
    'ten minutes' => [600, '10:00'],
    'hours' => [3665, '1:01:05'],
    'over a day' => [90061, '25:01:01'],
]);
