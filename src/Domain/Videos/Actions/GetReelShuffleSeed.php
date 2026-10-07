<?php

declare(strict_types=1);

namespace Domain\Videos\Actions;

use Domain\Videos\QueryBuilders\VideoQueryBuilder;
use Illuminate\Contracts\Session\Session;

/**
 * The seed that shuffles the reels feed. A new one starts every visit, and it's kept in the
 * session so the next pages continue the same order.
 */
class GetReelShuffleSeed
{
    public const string SESSION_KEY = 'reels.seed';

    public function handle(Session $session, bool $renew = false): int
    {
        if ($renew || ! $session->has(self::SESSION_KEY)) {
            $session->put(self::SESSION_KEY, random_int(1, VideoQueryBuilder::SHUFFLE_MODULUS - 1));
        }

        return (int) $session->get(self::SESSION_KEY);
    }
}
