<?php

declare(strict_types=1);

namespace Domain\Users\DataObjects;

use Spatie\LaravelData\Data;

class GeneralSettings extends Data
{
    public function __construct(
        public string $timezone = 'UTC',
        public string $locale = 'en_US',
        public string $language = 'en',
        public string $date_format = 'Y-m-d',
        public string $time_format = 'H:i',
    ) {}
}
