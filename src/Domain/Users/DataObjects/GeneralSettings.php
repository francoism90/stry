<?php

declare(strict_types=1);

namespace Domain\Users\DataObjects;

use Illuminate\Validation\Rule;
use Spatie\LaravelData\Data;

class GeneralSettings extends Data
{
    public function __construct(
        public string $timezone = 'UTC',
        public string $locale = 'en-US',
        public string $language = 'en',
        public string $date_format = 'YYYY-MM-DD',
        public string $time_format = 'HH:mm',
    ) {}

    /** @return array<string, mixed[]> */
    public static function rules(): array
    {
        return [
            'timezone' => ['sometimes', 'timezone'],
            'locale' => ['sometimes', 'string', Rule::in(['en-US', 'nl-NL'])],
            'language' => ['sometimes', 'string', Rule::in(['en'])],
            'date_format' => ['sometimes', 'string', Rule::in(['YYYY-MM-DD', 'MM/DD/YYYY', 'DD/MM/YYYY', 'DD.MM.YYYY', 'MMM D, YYYY'])],
            'time_format' => ['sometimes', 'string', Rule::in(['HH:mm', 'h:mm A', 'HH:mm:ss', 'h:mm:ss A'])],
        ];
    }
}
