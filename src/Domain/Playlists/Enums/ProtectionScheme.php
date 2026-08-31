<?php

declare(strict_types=1);

namespace Domain\Playlists\Enums;

use Domain\Shared\Contracts\Enumerable;

enum ProtectionScheme: string implements Enumerable
{
    case Cenc = 'cenc';
    case Cbcs = 'cbcs';

    public function label(): string
    {
        return self::labels()[$this->value];
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            'cenc' => 'CENC',
            'cbcs' => 'CBCS',
        ];
    }
}
