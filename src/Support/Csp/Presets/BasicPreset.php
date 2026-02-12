<?php

declare(strict_types=1);

namespace Support\Csp\Presets;

use Illuminate\Support\Facades\Config;
use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;
use Spatie\Csp\Policy;
use Spatie\Csp\Preset;
use Spatie\Csp\Value;

class BasicPreset implements Preset
{
    public function configure(Policy $policy): void
    {
        $s3Url = Config::string('filesystems.disks.s3.url');

        $policy
            ->add(Directive::BASE, Keyword::SELF)
            ->add(Directive::DEFAULT, Keyword::SELF)
            ->add(Directive::CONNECT, Keyword::SELF)
            ->add(Directive::SCRIPT, Keyword::SELF)
            ->add(Directive::STYLE, Keyword::SELF)
            ->add(Directive::STYLE, Keyword::UNSAFE_INLINE)
            ->add(Directive::STYLE, $s3Url)
            ->add(Directive::IMG, Keyword::SELF)
            // ->add(Directive::IMG, Keyword::DATA)
            ->add(Directive::IMG, $s3Url)
            ->add(Directive::FONT, Keyword::SELF)
            ->add(Directive::FONT, $s3Url)
            ->add(Directive::MEDIA, Keyword::SELF)
            ->add(Directive::OBJECT, Keyword::NONE)
            ->add(Directive::FORM_ACTION, Keyword::SELF)
            ->add(Directive::FRAME, Keyword::SELF);
    }
}
