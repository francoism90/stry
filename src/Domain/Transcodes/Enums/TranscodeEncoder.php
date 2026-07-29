<?php

declare(strict_types=1);

namespace Domain\Transcodes\Enums;

enum TranscodeEncoder: string
{
    case AV1 = 'av1';

    public function label(): string
    {
        return self::labels()[$this->value];
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            'av1' => 'ab-av1 (AV1)',
        ];
    }
}
