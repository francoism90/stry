<?php

declare(strict_types=1);

namespace Domain\Users\DataObjects;

use Spatie\LaravelData\Attributes\AutoClosureLazy;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

#[AutoClosureLazy]
class UserSettings extends Data
{
    public function __construct(
        public Lazy|UserGeneralSettings $general,
        public Lazy|UserAppearanceSettings $appearance,
    ) {}
}
