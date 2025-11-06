<?php

declare(strict_types=1);

namespace Domain\Users\DataObjects;

use Domain\Users\Models\User;
use Spatie\LaravelData\Attributes\AutoClosureLazy;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

#[AutoClosureLazy]
class UserSettings extends Data
{
    public function __construct(
        public Lazy|UserGeneralSettings|null $general = null,
        public Lazy|UserAppearanceSettings|null $appearance = null,
    ) {}

    public static function fromModel(User $user): self
    {
        /** @var UserSettings $settings */
        $settings = $user->settings;

        return new self(
            Lazy::create(fn() => UserGeneralSettings::from($settings->general?->toArray())),
            Lazy::create(fn() => UserAppearanceSettings::from($settings->appearance?->toArray())),
        );
    }
}
