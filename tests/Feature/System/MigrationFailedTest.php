<?php

declare(strict_types=1);

use Illuminate\Database\Events\MigrationFailed;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Support\System\ApplicationNotices;

it('logs which migration failed and checks the pending migrations again', function () {
    Log::spy();

    Cache::forever(ApplicationNotices::PendingMigrationsKey, ['fingerprint' => 'cached', 'pending' => true]);

    event(new MigrationFailed(
        new class extends Migration {},
        'up',
        '2026_11_12_000000_add_column_to_videos',
        new RuntimeException('Column already exists'),
    ));

    Log::shouldHaveReceived('error')
        ->once()
        ->with('Migration [2026_11_12_000000_add_column_to_videos] failed while running [up]: Column already exists');

    expect(Cache::has(ApplicationNotices::PendingMigrationsKey))->toBeFalse();
});
