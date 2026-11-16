<?php

declare(strict_types=1);

namespace Support\Octane;

use Illuminate\Support\Uri;

class CaddySites
{
    /**
     * Renders Caddyfile site blocks that let the app's own Caddy (FrankenPHP,
     * through "CADDY_EXTRA_CONFIG") reverse proxy the other services, so one
     * wildcard entry in your reverse proxy reaches every subdomain.
     *
     * Each block is pinned to "http://host:port": a bare hostname makes
     * Caddy try automatic HTTPS on port 443, which the app's non-root
     * Caddy can't bind.
     *
     * @param  array<string, string>  $sites  Public hostname => internal "host:port" upstream.
     */
    public static function render(array $sites, int $port): string
    {
        return collect($sites)
            ->filter(fn (string $upstream, string $host): bool => $host !== '' && $upstream !== '')
            ->map(fn (string $upstream, string $host): string => "http://{$host}:{$port} {\n\treverse_proxy {$upstream}\n}")
            ->implode("\n\n");
    }

    public static function hostFromUrl(string $url): string
    {
        if ($url === '') {
            return '';
        }

        return (string) Uri::of($url)->host();
    }

    /**
     * The "host:port" of a URL such as AWS_ENDPOINT. Empty when the URL has
     * no port, such as AWS S3 itself, which isn't proxied through the app.
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
     * "host:port", or an empty string when either is missing, which render()
     * skips.
     */
    public static function hostPort(?string $host, int|string|null $port): string
    {
        if (empty($host) || empty($port)) {
            return '';
        }

        return "{$host}:{$port}";
    }
}
