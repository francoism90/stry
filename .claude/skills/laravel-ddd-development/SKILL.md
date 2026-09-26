---
name: laravel-ddd-development
description: >
  Configure and apply the Laravel DDD package in Laravel applications.
license: MIT
metadata:
  author: foxws
---

# Laravel DDD

Use this skill when a Laravel application needs to integrate the `foxws/laravel-ddd` package.

## Primary Goal

- organize an application into DDD-style layers (`Domain`, `Modules`, `Foundation`, `Support`, `Infrastructure`, `Integrations`) instead of the default `app/` structure, and generate classes into them with `ddd:make`

## Workflow

### 1. Install and set up the layer structure

```bash
composer require foxws/laravel-ddd
php artisan ddd:install
```

`ddd:install` registers each layer's namespace in `composer.json`'s `autoload.psr-4`, creates the layer directories, and dumps the autoloader. Run it once, right after installing the package.

```bash
php artisan ddd:install --force            # overwrite namespaces that already point elsewhere
php artisan ddd:install --no-dump-autoload # skip regenerating the autoloader
```

### 2. Know the default layers

| Layer | Namespace | Path |
| --- | --- | --- |
| `Domain` | `Domain\` | `src/Domain` |
| `Modules` | `Modules\` | `src/Modules` |
| `Foundation` | `Foundation\` | `src/Foundation` |
| `Support` | `Support\` | `src/Support` |
| `Infrastructure` | `Infrastructure\` | `src/Infrastructure` |
| `Integrations` | `Integrations\` | `src/Integrations` |

`Domain` is framework-agnostic business logic; `Modules` is Laravel-facing code (controllers, requests, middleware) that orchestrates `Domain`; `Foundation` holds base classes, core providers, and shared helpers; `Support` holds generic cross-cutting helpers; `Infrastructure` holds concrete adapters to external systems (repositories, storage, queues); `Integrations` holds third-party service integrations (payments, notifications, etc.).

Publish `config/ddd.php` (`--tag=ddd-config`) to add, rename, or remove layers, or point one at `App\` to keep it under `app/`. Remove `Infrastructure`/`Integrations` from the published config if the app doesn't need that distinction — then drop the matching entry from `composer.json`'s `autoload.psr-4` too.

### 3. Generate classes into a layer

```bash
php artisan ddd:make CreateInvoice --type=action
# Domain\Invoice\Actions\CreateInvoice
```

The domain is guessed from the class name (`Invoice`) unless `--domain` is passed:

```bash
php artisan ddd:make Actions/CreateInvoice --type=action --domain=Billing
```

Layer-specific shortcuts skip the `--layer` option:

```bash
php artisan ddd:make-domain Invoice --type=model
php artisan ddd:make-module InvoiceController --type=controller
php artisan ddd:make-foundation AppServiceProvider --type=provider
php artisan ddd:make-support Money --type=value_object
```

`--type` selects both the stub and the subfolder generated into (`action`, `model`, `controller`, `job`, `event`, `listener`, `policy`, `provider`, `value_object`, and more — see `docs/domain-driven-design.md` for the full list).

### 4. Use path helpers instead of hardcoding layer paths

```php
domain_path('Invoice/Actions');   // base_path('src/Domain/Invoice/Actions')
modules_path('Web/Controllers');  // base_path('src/Modules/Web/Controllers')
foundation_path('Providers');     // base_path('src/Foundation/Providers')
support_path('Money');            // base_path('src/Support/Money')
infrastructure_path('Storage');   // base_path('src/Infrastructure/Storage')
integrations_path('Stripe');      // base_path('src/Integrations/Stripe')

layer_path('Billing', 'Invoices'); // for a custom or renamed layer
```

### 5. Customize subfolders and stubs (optional)

```php
// config/ddd.php
'substitutions' => [
    'action' => 'UseCases', // Domain\Invoice\Actions\... becomes Domain\Invoice\UseCases\...
],
'stubs' => [
    'action' => 'stubs/ddd/custom-action.stub',
],
```

Or publish every stub for whole-app overrides: `php artisan vendor:publish --tag=ddd-stubs` copies each `*.ddd.stub` into `stubs/`; delete the ones not being overridden.

## Rules, References, and Templates

Read before executing:

- `docs/domain-driven-design.md` in this package — layers, `ddd:make` usage, all `--type` values, customizing stubs and subfolders, path helpers
- `docs/configuration.md` in this package — every `config/ddd.php` option

## Examples

- A new app runs `ddd:install` right after requiring the package, then scaffolds a `Billing` domain with `php artisan ddd:make-domain Invoice --type=model` followed by `php artisan ddd:make CreateInvoice --type=action --domain=Billing`.
- An app that doesn't need a separate infrastructure layer removes `Infrastructure` from the published `config/ddd.php` and from `composer.json`'s `autoload.psr-4`, keeping only `Domain`, `Modules`, `Foundation`, and `Support`.

## Anti-patterns

- Do not hand-roll `app/` subfolder conventions once this package is installed — use `ddd:make` (or its layer-specific shortcuts) so generated classes land in the configured namespace and subfolder.
- Do not use this package for the opinionated Laravel defaults (`Model::shouldBeStrict()`, `URL::forceHttps()`, etc.) — that's the separate `foxws/laravel-essentials` package.
- Do not document package internals here; keep the skill focused on adoption in Laravel apps.
