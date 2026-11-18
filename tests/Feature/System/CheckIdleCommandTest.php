<?php

use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\artisan;

beforeEach(fn () => config(['queue.default' => 'redis']));

it('succeeds when no jobs are left', function () {
    artisan('app:idle')->assertSuccessful();
});

it('fails while jobs wait on a horizon queue', function () {
    Queue::push(fn () => null, '', 'processing');

    artisan('app:idle')
        ->expectsOutputToContain('1 jobs on redis-long:processing')
        ->assertFailed();
});

it('ignores queues on the sync driver', function () {
    // A queue no Horizon supervisor watches: the fake counts jobs by queue name, whatever the connection.
    config(['queue.default' => 'sync', 'queue.connections.sync.queue' => 'reports']);

    Queue::push(fn () => null, '', 'reports');

    artisan('app:idle')->assertSuccessful();
});
