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
        ->expectsOutputToContain('1 jobs on redis:processing')
        ->assertFailed();
});

it('ignores queues on the sync driver', function () {
    config(['queue.default' => 'sync']);

    Queue::push(fn () => null, '', 'default');

    artisan('app:idle')->assertSuccessful();
});
