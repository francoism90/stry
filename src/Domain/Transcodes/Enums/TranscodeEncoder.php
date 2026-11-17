<?php

declare(strict_types=1);

namespace Domain\Transcodes\Enums;

use Foxws\Essentials\Concerns\HasLabels;
use Foxws\Essentials\Contracts\Enumerable;

enum TranscodeEncoder: string implements Enumerable
{
    use HasLabels;

    case AV1 = 'av1';

    public function label(): string
    {
        return match ($this) {
            self::AV1 => 'ab-av1 (AV1)',
        };
    }
}
