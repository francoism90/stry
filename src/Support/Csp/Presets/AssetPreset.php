<?php

declare(strict_types=1);

namespace Support\Csp\Presets;

use Illuminate\Support\Facades\Config;
use Spatie\Csp\Directive;
use Spatie\Csp\Policy;
use Spatie\Csp\Preset;

class AssetPreset implements Preset
{
    public function configure(Policy $policy): void
    {
        $url = Config::string('filesystems.disks.s3.url');

        $policy
            ->add(Directive::FONT, $url)
            ->add(Directive::IMG, $url)
            ->add(Directive::STYLE, $url)
            ->addNonce(Directive::STYLE)
            ->addNonce(Directive::SCRIPT);
    }
}
