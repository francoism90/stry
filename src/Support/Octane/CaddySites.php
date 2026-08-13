<?php

declare(strict_types=1);

namespace Support\Octane;

use Illuminate\Support\Uri;

class CaddySites
{
    /**
     * Render Caddyfile site blocks that reverse proxy sibling services
     * through this container's FrankenPHP/Caddy instance, so a single
     * wildcard reverse-proxy entry upstream can reach every subdomain
     * without opening a host port or adding an entry per service.
     *
     * @param  array<string, string>  $sites  Public hostname => internal "host:port" upstream.
     */
    public static function render(array $sites): string
    {
        $blocks = [];

        foreach ($sites as $host => $upstream) {
            if ($host === '' || $upstream === '') {
                continue;
            }

            $blocks[] = "{$host} {\n\treverse_proxy {$upstream}\n}";
        }

        return implode("\n\n", $blocks);
    }

    public static function hostFromUrl(string $url): string
    {
        if ($url === '') {
            return '';
        }

        return (string) Uri::of($url)->host();
    }
}
