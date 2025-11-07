<?php

declare(strict_types=1);

namespace Domain\Users\DataObjects;

use Spatie\LaravelData\Data;

class UserAppearanceSettings extends Data
{
    public function __construct(
        public string $theme = 'dark',
        public string $default_view = 'vertical',
    ) {}
}
