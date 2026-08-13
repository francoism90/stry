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
     * Each block is pinned to "http://host:port", matching the app's own
     * site block -- a bare hostname makes Caddy attempt automatic HTTPS
     * (binding :443), which fails and crashes the whole server, since
     * this image's frankenphp binary has had CAP_NET_BIND_SERVICE
     * stripped and runs as a non-root user.
     *
     * @param  array<string, string>  $sites  Public hostname => internal "host:port" upstream.
     */
    public static function render(array $sites, int $port): string
    {
        $blocks = [];

        foreach ($sites as $host => $upstream) {
            if ($host === '' || $upstream === '') {
                continue;
            }

            $blocks[] = "http://{$host}:{$port} {\n\treverse_proxy {$upstream}\n}";
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

    /**
     * Extract "host:port" from a URL, e.g. AWS_ENDPOINT, for use as an
     * upstream in render(). Empty if the URL is empty or has no port
     * (e.g. real AWS S3, which isn't reverse proxied through the app).
     */
    public static function hostPortFromUrl(string $url): string
    {
        if ($url === '') {
            return '';
        }

        $uri = Uri::of($url);

        return self::hostPort($uri->host(), $uri->port());
    }

    /**
     * Build "host:port" for use as an upstream in render(), or an empty
     * string if either half is missing -- render() skips those.
     */
    public static function hostPort(?string $host, int|string|null $port): string
    {
        if (empty($host) || empty($port)) {
            return '';
        }

        return "{$host}:{$port}";
    }
}
