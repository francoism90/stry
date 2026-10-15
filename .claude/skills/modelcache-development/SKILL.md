---
name: modelcache-development
description: >
  Attach cached values to Eloquent models or model classes with foxws/laravel-modelcache, with a per-user cache namespace, cache profiles and the ModelCache facade.
license: MIT
metadata:
  author: francoism90
---

# Laravel Model Cache

Use this skill when a Laravel application caches values on Eloquent models with the `foxws/laravel-modelcache` package — e.g. a playback position, a random seed or expensive stats — or works with the `InteractsWithModelCache` trait, the `ModelCache` facade, a `CacheProfile` or `config/modelcache.php`.

## Concepts

- Values are stored in a Laravel cache store, keyed by the model (one record, or the whole class) and a key name.
- Each logged-in user gets their own namespace by default (the cache profile's `useCacheNameSuffix()` returns the user ID), so users never see each other's value for the same model. Guests and console share the unsuffixed namespace.
- A cache profile decides whether caching is enabled, which values are cached and until when (`modelcache.cache_lifetime_in_seconds`, default one week).

## Workflow

### 1. Install

- `composer require foxws/laravel-modelcache`
- Optionally `php artisan vendor:publish --tag="modelcache-config"`.
- `.env`: `MODEL_CACHE_ENABLED`, `MODEL_CACHE_STORE` (falls back to `CACHE_STORE`) and `MODEL_CACHE_LIFETIME` (seconds).

### 2. Add the trait

```php
use Foxws\ModelCache\Concerns\InteractsWithModelCache;

class Video extends Model
{
    use InteractsWithModelCache;
}
```

### 3. Cache per record

```php
$video->modelCache('playback_position', 142);                 // store
$video->modelCache('random_seed', 0.73, now()->addHours(6));  // TTL as date or seconds
$video->modelCached('playback_position', 0);                  // read, with default
$video->modelCacheHas('playback_position');
$video->modelCacheForget('playback_position');
$video->modelCacheRemember('stats', fn () => $this->computeStats($video), now()->addDay());
```

### 4. Cache per class

```php
Video::setModelCache('random_seed', 0.42, now()->addWeek());
Video::getModelCache('random_seed', 0.5);
Video::hasModelCache('random_seed');
Video::forgetModelCache('random_seed');
```

### 5. Use the facade outside models

```php
use Foxws\ModelCache\Facades\ModelCache;

if (ModelCache::enabled() && ModelCache::shouldCache($video, 'playback_position', $seconds)) {
    ModelCache::cache($video, 'playback_position', $seconds, now()->addDay());
}

ModelCache::getCachedValue($video, 'playback_position');
ModelCache::hasBeenCached($video, 'playback_position');
ModelCache::forget($video, ['playback_position', 'random_seed']);
```

### 6. Customize (optional)

- Override `shouldModelCache(string $key, mixed $value = null): bool` on a model to skip certain keys or values (e.g. `null`).
- Override `cacheNameSuffix(string $key): string` on a model to change the namespace: `''` shares the value between all users, `$key` separates by key.
- Set `modelcache.cache_profile` to a class extending `Foxws\ModelCache\CacheProfiles\BaseCacheProfile` (implement `shouldUseCache()` and `shouldCacheValue()`) to change the rules app-wide. `hasher` and `serializer` are swappable too.

## Rules

- Use this for small per-model values; don't use it as a query cache or to store whole models or collections.
- Remember the per-user default: a value cached in a request for user A isn't visible to user B, a guest, or a queued job. Override `cacheNameSuffix()` to `''` for values that must be shared.
- Pick a cache store that persists (Redis, database) when values must survive a deploy; `MODEL_CACHE_ENABLED=false` turns everything off.

## References

- [usage.md](https://github.com/foxws/laravel-modelcache/blob/main/docs/usage.md)
- [facade.md](https://github.com/foxws/laravel-modelcache/blob/main/docs/facade.md)
- [customization.md](https://github.com/foxws/laravel-modelcache/blob/main/docs/customization.md)
