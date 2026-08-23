<?php

declare(strict_types=1);

namespace Support\Csp\Presets;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Uri;
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
            ->add(Directive::BASE, Keyword::SELF)
            ->add(Directive::CONNECT, Keyword::SELF)
            ->add(Directive::CONNECT, Scheme::BLOB)
            ->add(Directive::DEFAULT, Keyword::SELF)
            ->add(Directive::FONT, Keyword::SELF)
            ->add(Directive::FORM_ACTION, Keyword::SELF)
            ->add(Directive::FRAME, Keyword::SELF)
            ->add(Directive::FRAME_ANCESTORS, Keyword::NONE)
            ->add(Directive::IMG, Keyword::SELF)
            ->add(Directive::IMG, Scheme::DATA)
            ->add(Directive::MEDIA, Keyword::SELF)
            ->add(Directive::MEDIA, Scheme::BLOB)
            ->add(Directive::OBJECT, Keyword::NONE)
            ->add(Directive::SCRIPT, Keyword::STRICT_DYNAMIC)
            ->add(Directive::STYLE, Keyword::SELF)
            ->add(Directive::STYLE, Keyword::UNSAFE_INLINE)
            ->add(Directive::CONNECT, ["https://*.{$host}", "wss://*.{$host}"])
            ->add([Directive::FRAME, Directive::FONT, Directive::STYLE, Directive::IMG], "*.{$host}")
            ->addNonce(Directive::SCRIPT);

        // The S3/rustfs and Reverb hosts aren't necessarily subdomains of the
        // app host (e.g. app on "stry.domain.tld" with storage/websockets on
        // sibling hosts like "stry-s3.domain.tld"), so add their actual
        // configured hosts explicitly rather than relying on "*.{$host}".
        if ($s3Host = $this->getS3Host()) {
            $policy
                ->add(Directive::CONNECT, "https://{$s3Host}")
                ->add([Directive::FRAME, Directive::FONT, Directive::STYLE, Directive::IMG, Directive::MEDIA], $s3Host);
        }

        if ($reverbHost = $this->getReverbHost()) {
            $policy->add(Directive::CONNECT, ["https://{$reverbHost}", "wss://{$reverbHost}"]);
        }
    }

    protected function getHost(): ?string
    {
        return Uri::of(Config::string('app.url', 'localhost'))->host();
    }

    protected function getS3Host(): ?string
    {
        return $this->resolveHost((string) Config::get('filesystems.disks.s3.url'));
    }

    protected function getReverbHost(): ?string
    {
        // "wsHost" is a bare hostname (e.g. "ws.laravel.test"), not a URL.
        $host = (string) Config::get('reverb.apps.apps.0.options.wsHost');

        return $host === '' ? null : $host;
    }

    protected function resolveHost(string $url): ?string
    {
        if ($url === '') {
            return null;
        }

        return Uri::of($url)->host();
    }
}
