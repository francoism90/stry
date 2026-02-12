<?php

declare(strict_types=1);

namespace Support\Csp\Presets;

use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;
use Spatie\Csp\Policy;
use Spatie\Csp\Preset;
use Spatie\Csp\Value;

class BasicPreset implements Preset
{
    public function configure(Policy $policy): void
    {
        $policy
            ->add(Directive::IMG, 'data:') // For inline SVGs
            ->add(Directive::STYLE, 'unsafe-hashes') // Required for Nuxt UI
            ->add(Directive::UPGRADE_INSECURE_REQUESTS, Value::NO_VALUE)
            ->add(Directive::BLOCK_ALL_MIXED_CONTENT, Value::NO_VALUE);
    }
}
