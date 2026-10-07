<?php

declare(strict_types=1);

use Domain\Videos\Actions\GetReelShuffleSeed;
use Domain\Videos\QueryBuilders\VideoQueryBuilder;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;

beforeEach(function () {
    $this->session = new Store('test', new ArraySessionHandler(10));
});

it('keeps the seed in the session for the next pages', function () {
    $seed = app(GetReelShuffleSeed::class)->handle($this->session);

    expect($seed)->toBeGreaterThan(0)->toBeLessThan(VideoQueryBuilder::SHUFFLE_MODULUS)
        ->and(app(GetReelShuffleSeed::class)->handle($this->session))->toBe($seed);
});

it('starts a new seed when asked to renew it', function () {
    $this->session->put(GetReelShuffleSeed::SESSION_KEY, 0);

    expect(app(GetReelShuffleSeed::class)->handle($this->session, renew: true))->toBeGreaterThan(0);
});
