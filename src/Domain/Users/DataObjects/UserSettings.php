<?php

declare(strict_types=1);

namespace Domain\Users\DataObjects;

use Domain\Users\Models\User;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

class UserSettings extends Data
{
    public function __construct(
        public Lazy|GeneralSettings|null $general = null,
        public Lazy|AppearanceSettings|null $appearance = null,
        public Lazy|PlayerSettings|null $player = null,
    ) {}

    public static function fromModel(User $user): self
    {
        /** @var UserSettings $settings */
        $settings = $user->settings;

        return new self(
            Lazy::create(fn () => GeneralSettings::from($settings->general?->toArray())),
            Lazy::create(fn () => AppearanceSettings::from($settings->appearance?->toArray())),
            Lazy::create(fn () => PlayerSettings::from($settings->player?->toArray())),
        );
    }
}
