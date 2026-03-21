<?php

declare(strict_types=1);

namespace Domain\Users\DataObjects;

use Spatie\LaravelData\Data;

class AppearanceSettings extends Data
{
    public function __construct(
        public string $theme = 'dark',
        public string $default_view = 'vertical',
    ) {}

    /** @return array<string, array<int, string>> */
    public static function rules(): array
    {
        return [
            'theme' => ['sometimes', 'string', 'in:dark,light,system'],
            'default_view' => ['sometimes', 'string', 'in:vertical,horizontal,grid'],
        ];
    }
}
