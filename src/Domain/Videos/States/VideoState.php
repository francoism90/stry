<?php

declare(strict_types=1);

namespace Domain\Videos\States;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class VideoState extends State
{
    abstract public function label(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Pending::class)
            ->allowAllTransitions();
    }
}
