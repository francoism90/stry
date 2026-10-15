---
name: scout-builder-development
description: >
  Build Laravel Scout search queries from API requests with foxws/laravel-scout-builder, using allowed filters, sorts and includes and JSON:API pagination.
license: MIT
metadata:
  author: francoism90
---

# Laravel Scout Builder

Use this skill when a Laravel application turns HTTP query parameters into Laravel Scout searches with the `foxws/laravel-scout-builder` package, or works with `ScoutBuilder`, `AllowedFilter`, `AllowedSort`, `AllowedInclude` or `config/scout-builder.php`.

## Concepts

- It is spatie/laravel-query-builder for Scout: the same `allowedFilters()` / `allowedSorts()` / `allowedIncludes()` style, on a Scout `Builder` instead of an Eloquent one.
- Only what you explicitly allow is accepted. An unknown filter, sort or include throws `InvalidFilterQuery`, `InvalidSortQuery` or `InvalidIncludeQuery` (all extend `InvalidArgumentException`), unless disabled in the config. Render them as a `400` in the app's exception handler if clients send these parameters.
- Request parameters: `?query=` (search term), `?filter[name]=`, `?sort=-a,b`, `?include=a,b` and `?page[number]=&page[size]=`. Scalars are cast (`'true'`, `'42'`, `'null'`).

## Workflow

### 1. Install

- `composer require foxws/laravel-scout-builder` (requires `laravel/scout`).
- The model needs Scout's `Searchable` trait.
- Optionally `php artisan vendor:publish --tag="scout-builder-config"`.

### 2. Build a search endpoint

```php
use Foxws\ScoutBuilder\AllowedFilter;
use Foxws\ScoutBuilder\AllowedSort;
use Foxws\ScoutBuilder\ScoutBuilder;

$posts = ScoutBuilder::for(Post::class, $request)
    ->allowedFilters(
        AllowedFilter::exact('status'),
        AllowedFilter::in('tags'),
        AllowedFilter::dynamicOperator('price'),
    )
    ->allowedSorts(
        AllowedSort::latest('recent', 'published_at'),
        AllowedSort::field('title'),
    )
    ->allowedIncludes('author')
    ->defaultSort('-recent')
    ->jsonPaginate();
```

- `ScoutBuilder::for()` takes a model class, a model, or an existing Scout builder (`Post::search('x')->where(...)`) to keep its conditions.
- Other Scout builder methods (`get()`, `paginate()`, `where()`, ...) are forwarded.
- Return the paginator through an API Resource or as an Inertia prop.

### 3. Filters

- `exact`, `in`, `notIn`, `trashed` (`with` / `only`), `operator($name, FilterOperator::GreaterThan)`, `dynamicOperator` (`?filter[price]=gte:120`; tokens `eq`, `neq`/`ne`, `lt`, `lte`, `gt`, `gte`), `scope` (calls `scopeOfCategory` for `of_category`), `callback` and `custom` (a class implementing `Foxws\ScoutBuilder\Filters\Filter`).
- Modifiers: `->default()`, `->nullable()`, `->ignore(...)`, `->delimiter()`.

### 4. Sorts

- `field`, `latest` / `oldest` (public name and column), `callback` and `custom` (implements `Foxws\ScoutBuilder\Sorts\Sort`); plain strings are `field` shorthand.
- `defaultSort()` / `defaultSorts()` apply only without a `sort` parameter; `->defaultDescending()` flips a sort's default direction.

### 5. Includes

`relationship` (`with()`), `count` (`withCount()`), `callback` and `custom` (implements `Foxws\ScoutBuilder\Includes\Includable`); plain strings are `relationship` shorthand.

### 6. Paginate

`jsonPaginate()` (with total) and `jsonSimplePaginate()` (next/previous only, cheaper) read `page[number]` and `page[size]`; override per call with `jsonPaginate(maxResults: 100, defaultSize: 50)`. Scout has no cursor pagination.

## Rules

- Includes and `scope` filters run through Scout's `query()` callback, which only the `database` and `collection` drivers call. Algolia, Meilisearch and Typesense silently ignore them, so don't rely on them there.
- Engine awareness (`scout-builder.engine_awareness`, on when `APP_DEBUG` is true) throws `UnsupportedEngineFeature` when operator filters or field sorts are used on a driver outside `operator_filter_drivers` / `field_sort_drivers`. Use `EngineAwareness::ensureFeatureSupport()` in custom filters or sorts.
- Custom includes must wrap the existing `queryCallback` rather than replace it, so chained includes keep working.
- Remote engines only filter and sort on attributes configured as filterable/sortable in the index; configure those in `config/scout.php`.

## References

- [usage.md](https://github.com/foxws/laravel-scout-builder/blob/main/docs/usage.md)
- [filters.md](https://github.com/foxws/laravel-scout-builder/blob/main/docs/filters.md)
- [sorts.md](https://github.com/foxws/laravel-scout-builder/blob/main/docs/sorts.md)
- [includes.md](https://github.com/foxws/laravel-scout-builder/blob/main/docs/includes.md)
- [pagination.md](https://github.com/foxws/laravel-scout-builder/blob/main/docs/pagination.md)
- [engine-awareness.md](https://github.com/foxws/laravel-scout-builder/blob/main/docs/engine-awareness.md)
