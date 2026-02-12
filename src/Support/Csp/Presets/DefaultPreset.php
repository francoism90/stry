<?php

declare(strict_types=1);

namespace Support\Csp\Presets;

use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;
use Spatie\Csp\Policy;
use Spatie\Csp\Preset;

class DefaultPreset implements Preset
{
    public function configure(Policy $policy): void
    {
        $policy
            ->add(Directive::CONNECT, 'api.iconify.design');
    }
}
