<?php

declare(strict_types=1);

namespace Domain\Shared\Enums;

use Domain\Shared\Contracts\Enumerable;

enum DiskDriver: string implements Enumerable
{
    case Local = 'local';
    case S3 = 's3';

    public function label(): string
    {
        return self::labels()[$this->value];
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            'local' => __('Local'),
            's3' => __('S3 compatible'),
        ];
    }
}
