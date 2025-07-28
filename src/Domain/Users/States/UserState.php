<?php

declare(strict_types=1);

namespace Domain\Users\States;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class UserState extends State
{
    abstract public function label(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Pending::class)
            ->allowAllTransitions();
    }
}
