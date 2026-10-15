<?php

declare(strict_types=1);

namespace Domain\Shared\DataObjects;

use SensitiveParameter;
use Spatie\LaravelData\Data;

class StorageCredentials extends Data
{
    public function __construct(
        public string $key = '',
        #[SensitiveParameter]
        public string $secret = '',
    ) {}

    /**
     * The access key with only its first and last four characters visible.
     */
    public function maskedKey(): string
    {
        if (strlen($this->key) <= 8) {
            return str_repeat('•', strlen($this->key));
        }

        return substr($this->key, 0, 4).'…'.substr($this->key, -4);
    }

    public function hasSecret(): bool
    {
        return $this->secret !== '';
    }
}
