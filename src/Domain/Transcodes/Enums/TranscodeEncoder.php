<?php

declare(strict_types=1);

namespace Domain\Transcodes\Enums;

use Domain\Shared\Contracts\Enumerable;

enum TranscodeEncoder: string implements Enumerable
{
    case AV1 = 'av1';

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            'av1' => 'ab-av1 (AV1)',
        ];
    }
}
