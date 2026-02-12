<?php

declare(strict_types=1);

namespace Support\Csp\Presets;

use Illuminate\Support\Facades\Config;
use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;
use Spatie\Csp\Policy;
use Spatie\Csp\Preset;
use Spatie\Csp\Scheme;
use Spatie\Csp\Value;

class BasicPreset implements Preset
{
    public function configure(Policy $policy): void
    {
        // Get the host from the app URL configuration
        $host = $this->getHost();

        // Configure the CSP policy with directives and sources
        $policy
            ->add(Directive::UPGRADE_INSECURE_REQUESTS, Value::NO_VALUE)
            ->add(Directive::BLOCK_ALL_MIXED_CONTENT, Value::NO_VALUE)
            ->add(Directive::CONNECT, ["https://*.{$host}", "wss://*.{$host}"])
            ->add([Directive::FRAME, Directive::SCRIPT, Directive::FONT, Directive::STYLE, Directive::IMG], "*.{$host}")
            ->add(Directive::BASE, Keyword::SELF)
            ->add(Directive::CONNECT, Keyword::SELF)
            ->add(Directive::DEFAULT, Keyword::SELF)
            ->add(Directive::FONT, Keyword::SELF)
            ->add(Directive::FORM_ACTION, Keyword::SELF)
            ->add(Directive::FRAME, Keyword::SELF)
            ->add(Directive::IMG, Keyword::SELF)
            ->add(Directive::MEDIA, Keyword::SELF)
            ->add(Directive::MEDIA, Scheme::BLOB)
            ->add(Directive::OBJECT, Keyword::NONE)
            ->add(Directive::SCRIPT, Keyword::STRICT_DYNAMIC)
            ->add(Directive::SCRIPT, Keyword::SELF)
            ->add(Directive::SCRIPT, Keyword::UNSAFE_INLINE)
            ->add(Directive::STYLE, Keyword::SELF)
            ->add(Directive::STYLE, Keyword::UNSAFE_INLINE)
            ->addNonce(Directive::SCRIPT);
    }

    protected function getHost(): string
    {
        return parse_url(Config::string('app.url', 'localhost'), PHP_URL_HOST);
    }
}
