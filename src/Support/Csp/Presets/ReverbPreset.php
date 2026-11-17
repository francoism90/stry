<?php

declare(strict_types=1);

namespace Support\Csp\Presets;

use Illuminate\Support\Facades\Config;
use Spatie\Csp\Directive;
use Spatie\Csp\Policy;
use Spatie\Csp\Preset;

/**
 * Lets Echo connect to Reverb, which usually runs on its own port or host and
 * isn't covered by "self" or the app's subdomains.
 */
class ReverbPreset implements Preset
{
    public function configure(Policy $policy): void
    {
        if ($origin = $this->origin()) {
            $policy->add(Directive::CONNECT, $origin);
        }
    }

    protected function origin(): ?string
    {
        $host = Config::string('reverb.apps.apps.0.options.wsHost', '');

        if ($host === '') {
            return null;
        }

        $isSecure = Config::string('reverb.apps.apps.0.options.wsScheme', 'https') === 'https';
        $port = Config::integer('reverb.apps.apps.0.options.wsPort', $isSecure ? 443 : 80);

        $scheme = $isSecure ? 'wss' : 'ws';

        return $port === ($isSecure ? 443 : 80)
            ? "{$scheme}://{$host}"
            : "{$scheme}://{$host}:{$port}";
    }
}
