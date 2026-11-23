---
name: scout-relations-development
description: >
  Keep Laravel Scout indexes fresh by re-indexing searchable related models when a parent model is saved or deleted, with foxws/laravel-scout-relations.
license: MIT
metadata:
  author: francoism90
---

# Laravel Scout Relations

Use this skill when a Laravel application re-indexes related Scout models after a parent model changes with the `foxws/laravel-scout-relations` package — e.g. re-indexing an author's posts when the author is renamed — or works with the `HasSearchableRelations` trait, the `scout:index-relations` command or `config/scout-relations.php`.

## Concepts

- The **parent** model (e.g. `Author`) uses `HasSearchableRelations` and lists relationship names in `searchableRelations()`.
- The **related** models (e.g. `Post`) use Scout's `Searchable` trait and usually copy parent data into `toSearchableArray()`.
- When the parent changes, every related model of each listed relation is pushed to the search engine again with `searchable()`, or removed from it when its `shouldBeSearchable()` returns `false`.

## Workflow

### 1. Install

- `composer require foxws/laravel-scout-relations`
- Optionally publish the config: `php artisan vendor:publish --tag="scout-relations-config"`.

### 2. Watch relations on the parent

```php
use Foxws\ScoutRelations\Concerns\HasSearchableRelations;

class Author extends Model
{
    use HasSearchableRelations;

    /**
     * @return array<int, string>
     */
    public function searchableRelations(): array
    {
        return ['posts'];
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
```

- Return relationship **method names**, not classes. Any relation that supports `chunkById()` works (`hasMany`, `belongsToMany`, `morphMany`, `belongsTo`, ...).
- Relations whose related model doesn't use `Searchable` are skipped silently, so check the related model when nothing gets indexed.

### 3. When re-indexing happens

| Event | Behaviour |
| --- | --- |
| `saved` | Only when the save changed the model; no-op saves are skipped. |
| `deleted` | So the index reflects the parent's removal. The related keys are read before the delete, so pivot rows the database cascades away are covered. |

- Override `shouldReindexSearchableRelations(): bool` to skip re-indexing conditionally, e.g. only when indexed columns changed:

```php
public function shouldReindexSearchableRelations(): bool
{
    // After a delete wasChanged() is false; trashed() exists on models that use SoftDeletes.
    return $this->wasChanged('name') || ! $this->exists || $this->trashed();
}
```

- Call `$author->reindexSearchableRelations()` to re-index one parent's relations manually (e.g. after a `saveQuietly()` or a mass update).

### 4. Avoid N+1 queries while indexing

Related models are re-indexed in chunks of their keys. If the related model defines `makeAllSearchableUsing()`, it is applied to each chunk's query as a scope, just like Scout's bulk import. Joins and `orWhere()` calls in it can't widen the query beyond the related models:

```php
protected function makeAllSearchableUsing(Builder $query): Builder
{
    return $query->with('author');
}
```

### 5. Re-index everything

```bash
php artisan scout:index-relations "App\Models\Author"
```

- Runs `makeAllSearchable()` once per distinct searchable related class of the model's `searchableRelations()` — a full import of those classes, not a per-parent loop — so a `belongsToMany` shared by many parents is only indexed once. For a `morphTo` relation, those are the classes its records point to.
- Works even when `scout-relations.enabled` is `false`.

### 6. Configure (optional)

| Key | Env variable | Default |
| --- | --- | --- |
| `enabled` | `SCOUT_RELATIONS_ENABLED` | `true` |
| `chunk.searchable` | `SCOUT_RELATIONS_CHUNK_SEARCHABLE` | `500` (also used when not positive) |
| `chunk.unsearchable` | `SCOUT_RELATIONS_CHUNK_UNSEARCHABLE` | `500` (reserved, unused) |

## Rules

- Don't add your own `saved`/`deleted` observers to re-index the same relations; the trait already does.
- `enabled` is read when the model class **boots**, once per process. Changing it at runtime (or in a test after the model was used) has no effect until the models are booted again — in tests, set it before the first use or call `Model::clearBootedModels()`.
- Updates that skip model events (`Author::query()->update()`, `saveQuietly()`, a parent deleted by a database cascade) don't trigger re-indexing. Call `reindexSearchableRelations()` or the Artisan command afterwards. Related rows that a database cascade deletes stay in the search index, since Scout never sees them deleted.
- Creating a parent doesn't re-index; a child moved to another parent isn't removed from the old parent's relations until that parent is re-indexed.
- With `scout.after_commit` enabled, re-indexing inside a transaction waits for the commit and is skipped on a rollback. The related keys are still read inside the save or delete; enable `scout.queue` so `searchable()` dispatches jobs instead of indexing in the request.
- `withoutSyncingToSearch()` on the related model, `shouldBeSearchable()` and `scout.soft_delete` are honoured like in Scout's own observer.
- In `toSearchableArray()`, read the parent null-safely (`$this->author?->name`): after the parent is deleted, its related models are re-indexed without it.
- Mutual relations are safe: a per-class guard (scoped per request, so safe under Octane) stops a cascade from re-entering a class that is already re-indexing.
- In tests, use `Queue::fake()` with `scout.queue` enabled and assert `MakeSearchable` (and `RemoveFromSearch`) jobs, rather than hitting a real search engine.

## References

- [Documentation](https://foxws.github.io/laravel-scout-relations/)
