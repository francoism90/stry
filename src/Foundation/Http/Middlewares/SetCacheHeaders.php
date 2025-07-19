<?php

namespace Foundation\Http\Middlewares;

use Closure;
use Illuminate\Http\Middleware\SetCacheHeaders as Middleware;
use Illuminate\Support\Carbon;

class SetCacheHeaders extends Middleware
{
    public function handle($request, Closure $next, $options = [])
    {
        $response = $next($request);

        if (! $request->isMethodCacheable()) {
            return $response;
        }

        if (is_string($options)) {
            $options = $this->parseOptions($options);
        }

        if (! $response->isSuccessful()) {
            return $response;
        }

        if (isset($options['etag']) && $options['etag'] === true) {
            $options['etag'] = $response->getEtag() ?? md5($response->getContent());
        }

        if (isset($options['last_modified'])) {
            if (is_numeric($options['last_modified'])) {
                $options['last_modified'] = Carbon::createFromTimestamp($options['last_modified'], date_default_timezone_get());
            } else {
                $options['last_modified'] = Carbon::parse($options['last_modified']);
            }
        }

        $response->setCache($options);
        $response->isNotModified($request);

        return $response;
    }
}
