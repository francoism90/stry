<?php

declare(strict_types=1);

namespace Domain\Users\DataObjects;

use Domain\Shared\Enums\Language;
use Domain\Shared\Enums\Locale;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Spatie\LaravelData\Data;

class GeneralSettings extends Data
{
    public function __construct(
        public string $timezone = 'UTC',
        public Locale $locale = Locale::EnUs,
        public Language $language = Language::English,
        public string $date_format = 'YYYY-MM-DD',
        public string $time_format = 'HH:mm',
    ) {}

    /** @return array<string, mixed[]> */
    public static function rules(): array
    {
        return [
            'timezone' => ['sometimes', 'timezone'],
            'locale' => ['sometimes', new Enum(Locale::class)],
            'language' => ['sometimes', new Enum(Language::class)],
            'date_format' => ['sometimes', 'string', Rule::in(['YYYY-MM-DD', 'MM/DD/YYYY', 'DD/MM/YYYY', 'DD.MM.YYYY', 'MMM D, YYYY'])],
            'time_format' => ['sometimes', 'string', Rule::in(['HH:mm', 'h:mm A', 'HH:mm:ss', 'h:mm:ss A'])],
        ];
    }
}
