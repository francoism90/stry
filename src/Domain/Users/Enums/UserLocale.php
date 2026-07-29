<?php

declare(strict_types=1);

namespace Domain\Users\Enums;

enum UserLocale: string
{
    case English = 'en';
    case Dutch = 'nl';

    public function label(): string
    {
        return self::labels()[$this->value];
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            'en' => __('English'),
            'nl' => __('Dutch'),
        ];
    }
}
