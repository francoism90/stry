---
name: pwa-development
description: >
  Turn a Laravel app into an installable Progressive Web App with foxws/laravel-pwa, including the manifest, icons, the service worker and its Blade directives.
license: MIT
metadata:
  author: francoism90
---

# Laravel PWA

Use this skill when a Laravel application becomes a Progressive Web App (PWA) with the `foxws/laravel-pwa` package, or works with `@pwaHead`, `@pwaSw`, `php artisan pwa:generate`, `public/manifest.json`, `public/sw.js` or `config/pwa.php`.

## Concepts

- `pwa:generate` writes `public/manifest.json` from `config('pwa.manifest')` and `config('pwa.icons')`, and copies the service worker stub to `public/sw.js`.
- The service worker fetches pages network first (never stale) and static assets cache first. It skips the cache for Inertia (`X-Inertia`), Livewire (`X-Livewire`, `X-Livewire-Navigate`) and htmx (`HX-Request`) requests, and for the paths in `pwa.ignore_paths`.
- Manifest and service worker URLs get a `?v=` suffix from the file's last-modified time, so every `pwa:generate` run busts stale caches.

## Workflow

### 1. Install

- `composer require foxws/laravel-pwa`
- `php artisan vendor:publish --tag="pwa-config"` to customize `config/pwa.php`.

### 2. Add the Blade directives

Put `@pwaHead` inside `<head>` and `@pwaSw` just before `</body>` in the app's root layout (e.g. `resources/views/app.blade.php` for Inertia):

```blade
<head>
    @pwaHead
</head>

<body>
    ...
    @pwaSw
</body>
```

- `@pwaHead` adds the theme-color meta tag, the apple-touch-icon and the manifest link. Override with `@pwaHead(['themeColor' => '#ff0000', 'manifest' => '/custom.json', 'icon' => '/icon.png'])`.
- `@pwaSw` registers the service worker. Override with `@pwaSw(['swPath' => '/sw.js', 'scope' => '/', 'debug' => true, 'updateInterval' => 12])`. It picks up `Vite::cspNonce()` automatically.
- These are Blade includes, not components: don't use `<x-pwa-head>` or `<x-pwa-sw>`.

### 3. Configure the manifest and icons

- Set the manifest through `.env` (`PWA_SHORT_NAME`, `PWA_DESCRIPTION`, `PWA_THEME_COLOR`, `PWA_BACKGROUND_COLOR`, `PWA_DISPLAY`, `PWA_START_URL`, ...); `name` comes from `APP_NAME`.
- Any key added to the `manifest` array (`shortcuts`, `screenshots`, `categories`, ...) is included; a key set to `null` is left out.
- `icons` is a list of `disk`, `path`, `sizes` and `type`. With `disk` set, the URL is resolved with `Storage::disk()->url()` at generation time; with `null`, `path` is used as-is. The defaults expect a 192×192 and a 512×512 PNG plus an apple-touch-icon under `/storage/images/icons/`, so run `php artisan storage:link` and add those files.

### 4. Generate

```bash
php artisan pwa:generate
```

Run it after every config change and on deploy. Create `public/offline.html` yourself; the service worker shows it when there's no connection (see `examples/offline.html` in the package repository).

### 5. Disable when needed

Set `PWA_ENABLED=false` (e.g. local or staging) and run `pwa:generate`: it writes a self-unregistering service worker that clears its caches and removes itself. The Blade templates and `manifest.json` stay unchanged.

## Rules

- Don't write `manifest.json` or `sw.js` by hand; change `config/pwa.php` and run `pwa:generate`.
- Add new XHR or API prefixes the service worker must never cache to `pwa.ignore_paths`.
- `pwa.update_interval` is in hours (default `24`); `0` turns off periodic update checks.

## References

- [usage.md](https://github.com/foxws/laravel-pwa/blob/main/docs/usage.md)
- [configuration.md](https://github.com/foxws/laravel-pwa/blob/main/docs/configuration.md)
