<?php

declare(strict_types=1);

namespace Support\Csp\Nonce;

use Illuminate\Support\Facades\Vite;
use Spatie\Csp\Nonce\NonceGenerator;

class LaravelViteNonceGenerator implements NonceGenerator
{
    public function generate(): string
    {
        return Vite::cspNonce();
    }
}
