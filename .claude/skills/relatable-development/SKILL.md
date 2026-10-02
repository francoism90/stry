---
name: relatable-development
description: >
  Relate Eloquent models to other models with foxws/laravel-relatable, using a score and boost to control their priority.
license: MIT
metadata:
  author: francoism90
---

# Laravel Relatable

Use this skill when a Laravel application relates models to other models with the `foxws/laravel-relatable` package — e.g. related tags, related videos, "see also" links — or works with the `InteractsWithRelated` trait, the `Relatable` model or `config/relatable.php`.

## Concepts

- A relation is a row in `relatables`: from a `relatable` morph (A) to a `related` morph (B).
- Relations are **directed**. A → B doesn't imply B → A; when both exist, each has its own `score` and `boost`.
- **weight = score × boost**. Related models are ordered by weight, highest first. `score` is the base relevance (typically 0–1); `boost` is a multiplier (default 1.0) to promote or demote a relation without losing its score.

## Workflow

### 1. Install

- `composer require foxws/laravel-relatable`
- The `relatables` migration runs automatically. Don't write your own migration for it.
- Add `use Foxws\Relatable\Concerns\InteractsWithRelated;` to each model that relates to others.

### 2. Relate models

```php
$a->attachRelated($b);                                   // score/boost default to 1.0
$a->attachRelated($b, score: 0.5, boost: 2.0);           // re-attaching updates the row
$a->attachRelated($b, score: 1.0, mutual: true, mutualScore: 0.5); // also B → A
$a->detachRelated($b, mutual: true);                     // both A → B and B → A
$a->syncRelated([$b, ['model' => $c, 'score' => 0.5]]);  // exact set; plain models keep their score
```

#### Mutual relations

Pass `mutual: true` when both models should list each other in `relates`; without it only A → B is written.

- `attachRelated(..., mutual: true)` writes A → B and B → A in one transaction. B → A uses `mutualScore` / `mutualBoost`, falling back to `score` / `boost`. Both rows get the same `options`.
- `detachRelated($b, mutual: true)` removes both rows; without `mutual` B → A stays.
- `syncRelated($items, mutual: true)` has no `mutualScore` / `mutualBoost`: each B → A gets its item's score and boost. For a different score back, call `attachRelated()` per model instead.
- Sync only removes a B → A along with the A → B it removes. A B → A that exists without A → B is kept, because it was created independently — detach it explicitly if it must go.
- Only `$a`'s loaded relations are refreshed. If `$b` already has `relatables` or `relates` loaded, call `$b->refresh()` before reading them.

### 3. Read related models

- `$a->relates` — cached attribute, a `Collection` of models, highest weight first. Works with `append('relates')` in API resources.
- `$a->getRelates(Video::class)` — only one type (class or morph alias).
- Eager load for lists: `->with('relatables.related')` to avoid N+1 queries.
- `relatables()` (A → others) and `relatablesFrom()` (others → A) are `MorphMany` relations to `Relatable`.

### 4. Query relations

`Relatable::query()` has `whereRelatable($model)`, `whereRelated($model)`, `whereRelatedType($class)`, `minWeight(float)`, `orderByWeight('desc')` and `withWeight()`. A result collection has `sortByWeight()` and `models()`.

### 5. Customize (optional)

- `relatable.models.relatable`: a subclass of `Foxws\Relatable\Models\Relatable`. It may set `$table` to keep an existing table; then set `relatable.migrations` to `false`.
- `relatable.defaults.score` / `relatable.defaults.boost`: values for new relations.
- `relatable.delete_on_model_delete`: deleting a model removes its relations both ways (soft deletes keep them until force-deleted).

## Rules

- Always resolve the row model via `Relatable::modelClass()`, never `Relatable::class` directly, so a configured subclass is honored.
- Pass scores as floats; don't store the weight — it is always derived from `score × boost`.

## References

- [usage.md](../../../../docs/usage.md)
- [scoring.md](../../../../docs/scoring.md)
- [configuration.md](../../../../docs/configuration.md)
