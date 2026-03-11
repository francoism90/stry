<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\CreatesApplication;

expect()
    ->extend('toBeSameModel', fn (Model $model) => $this->is($model)->toBeTrue());

uses(TestCase::class, CreatesApplication::class, RefreshDatabase::class)
    ->beforeEach(function () {
        // Make sure we do not run on production
        throw_if(app()->environment() === 'production');

        // Fake instances
        Bus::fake();
        Mail::fake();
        Notification::fake();
        Queue::fake();
        Storage::fake();

        // Setup database
        $this->seed();
    })
    ->in(__DIR__);
