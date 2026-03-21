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

    /** @return array<string, array<int, string>> */
    public static function rules(): array
    {
        return [
            'timezone' => ['sometimes', 'string', 'max:255'],
            'locale' => ['sometimes', 'string', 'max:255'],
            'language' => ['sometimes', 'string', 'max:255'],
            'date_format' => ['sometimes', 'string', 'max:255'],
            'time_format' => ['sometimes', 'string', 'max:255'],
        ];
    }
}
