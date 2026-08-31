<?php

declare(strict_types=1);

namespace Domain\Playlists\Enums;

use Domain\Shared\Contracts\Enumerable;

enum EncryptionMethod: string implements Enumerable
{
    case RawKeyEncryption = 'raw_key_encryption';
    case ClearKey = 'clearkey';

    public function label(): string
    {
        return self::labels()[$this->value];
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            'raw_key_encryption' => __('Raw key encryption'),
            'clearkey' => __('Clear key'),
        ];
    }
}
