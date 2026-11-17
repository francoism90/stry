<?php

declare(strict_types=1);

namespace Domain\Shared\Enums;

use Foxws\Essentials\Concerns\HasLabels;
use Foxws\Essentials\Contracts\Enumerable;

enum DiskDriver: string implements Enumerable
{
    use HasLabels;

    case Local = 'local';
    case S3 = 's3';

    public function label(): string
    {
        return match ($this) {
            self::Local => __('Local'),
            self::S3 => __('S3 compatible'),
        };
    }
}
