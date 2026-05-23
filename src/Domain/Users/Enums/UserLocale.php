<?php

declare(strict_types=1);

namespace Domain\Users\Enums;

use Domain\Shared\Contracts\Enumerable;

enum UserLocale: string implements Enumerable
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

    /** @return array<int, array{label: string, value: string}> */
    public static function options(): array
    {
        return array_map(
            fn (self $case) => ['label' => $case->label(), 'value' => $case->value],
            self::cases(),
        );
    }
}
