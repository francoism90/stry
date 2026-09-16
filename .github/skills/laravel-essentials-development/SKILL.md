---
name: laravel-essentials-development
description: >
  Configure and apply the Laravel Essentials package in Laravel applications.
license: MIT
metadata:
  author: foxws
---

# Laravel Essentials

Use this skill when a Laravel application needs to integrate the `foxws/laravel-essentials` package.

## Primary Goal

- apply a curated set of opinionated Laravel defaults ("configurables") on boot, and let apps enable, disable, reorder, or extend them without touching package internals

## Workflow

### 1. Install and configure

- `composer require foxws/laravel-essentials`
- No steps required beyond installing: `Foxws\Essentials\Essentials::configure()` runs automatically once the application has booted
- Optionally publish `config/essentials.php` (`--tag=essentials-config`) to change which configurables run or to set a morph map

### 2. Know what runs by default

Every configurable implements `Foxws\Essentials\Contracts\Configurable` (`enabled(): bool`, `configure(): void`). Out of the box:

| Configurable | Enabled when | What it does |
| --- | --- | --- |
| `AggressivePrefetching` | Always | `Vite::useAggressivePrefetching()` |
| `AutomaticallyEagerLoadRelationships` | Always | `Model::automaticallyEagerLoadRelationships()` |
| `EnforceMorphMap` | `essentials.morph_map` is not empty | `Relation::enforceMorphMap(...)` |
| `FakeSleep` | Running unit tests | `Sleep::fake()` |
| `ForceHttpsScheme` | Always | `URL::forceHttps()` |
| `ForceSecurePassword` | Running in production | `Password::defaults()` — 12–64 chars, mixed case, numbers, symbols, "uncompromised" |
| `ImmutableDates` | Always | `Date::use(CarbonImmutable::class)` |
| `ModelShouldBeStrict` | Always | `Model::shouldBeStrict()` |
| `ModelUnguard` | Opt-in (commented out by default) | `Model::unguard()` |
| `PreventStrayRequests` | Running unit tests | `Http::preventStrayRequests()` |
| `ProhibitDestructiveCommands` | Running in production | `DB::prohibitDestructiveCommands()` |
| `ResourceWithoutWrapping` | Always | `JsonResource::withoutWrapping()` |

If an app's existing behavior conflicts with one of the "Always" defaults (e.g. it relies on lazy loading or wrapped JSON resources), publish the config and remove that configurable rather than working around it downstream.

### 3. Enable, disable, or reorder configurables

Publish the config, then edit the `configurables` array:

```bash
php artisan vendor:publish --tag="essentials-config"
```

```php
// config/essentials.php
'configurables' => [
    Foxws\Essentials\Configurables\ForceHttpsScheme::class,
    Foxws\Essentials\Configurables\ImmutableDates::class,
    // ...
],
```

### 4. Set a morph map (optional)

```php
// config/essentials.php
'morph_map' => [
    'user' => App\Models\User::class,
    'post' => Domain\Posts\Models\Post::class,
],
```

`EnforceMorphMap` only enables itself once this array is non-empty.

### 5. Add an app- or package-specific configurable (optional)

```php
namespace App\Configurables;

use Foxws\Essentials\Contracts\Configurable;
use Illuminate\Support\Facades\Date;

final readonly class UseUtcTimezone implements Configurable
{
    public function enabled(): bool
    {
        return true;
    }

    public function configure(): void
    {
        Date::setTestNow();
    }
}
```

Add it to `config('essentials.configurables')`, or register it at runtime — e.g. from another package's own service provider, so that package doesn't need the consuming app to edit config at all:

```php
use App\Configurables\UseUtcTimezone;
use Foxws\Essentials\Essentials;

Essentials::extend(UseUtcTimezone::class);

// A class-string is resolved through the container; an instance works too.
Essentials::extend(new UseUtcTimezone);
```

## Rules, References, and Templates

Read before executing:

- `docs/configurables.md` in this package — full configurable list and how to write your own
- `docs/configuration.md` in this package — every `config/essentials.php` option

## Examples

- A new Laravel app installs the package and gets HTTPS URLs, immutable dates, strict models, and unwrapped JSON resources with zero configuration; it publishes the config only to turn off `ResourceWithoutWrapping` because an existing API contract expects the `data` wrapper.
- A separate first-party package calls `Essentials::extend(new EnforceQueueConnection)` from its own service provider so every app that installs both packages gets the behavior automatically, without editing `config/essentials.php`.

## Anti-patterns

- Do not call `Essentials::configure()` yourself — it already runs on `booted()` and is idempotent; calling it again is a no-op, not a way to force re-configuration.
- Do not use this package for Domain Driven Design scaffolding (`ddd:install`, `ddd:make`) — that lives in the separate `foxws/laravel-ddd` package.
- Do not document package internals here; keep the skill focused on adoption in Laravel apps.
