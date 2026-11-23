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
- Only what you explicitly allow is accepted. An unknown filter, sort or include throws `InvalidFilterQuery`, `InvalidSortQuery` or `InvalidIncludeQuery` (all extend `InvalidArgumentException`), unless disabled in the config. So does `InvalidFilterValue`, for a value a filter can't use. Render them as a `400` in the app's exception handler if clients send these parameters.
- Request parameters: `?query=` (search term), `?filter[name]=`, `?sort=-a,b`, `?include=a,b` and `?page[number]=&page[size]=`. Scalars are cast (`'true'`, `'42'`, `'null'`), also after validation: read it from the `FormRequest` with `validated()` where the exact string matters, such as a `007` code.

## Workflow

### 1. Install

- `composer require foxws/laravel-scout-builder` (requires `laravel/scout` 11.10 or newer).
- The model needs Scout's `Searchable` trait.
- Optionally `php artisan vendor:publish --tag="scout-builder-config"`.

### 2. Validate in a FormRequest

Allowing a filter doesn't validate its value. Create a `FormRequest` for each search endpoint (`php artisan make:request SearchPostsRequest`), with a rule for every allowed filter that doesn't validate itself:

```php
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchPostsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // or check that the user may search
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'query' => ['nullable', 'string', 'max:100'],
            'filter' => ['array:status,tags,price'],
            'filter.status' => ['string', Rule::in(['draft', 'published'])],
            'filter.tags' => ['string', 'regex:/^[a-z0-9,-]+$/'],
            'filter.price' => ['string', 'regex:/^((gte|lte|gt|lt):)?\d+$/'],
        ];
    }
}
```

`array:status,tags,price` turns an unknown filter into a `422`, before the builder would throw.

### 3. Build a search endpoint

Type-hint the `FormRequest` in the controller, so it validates before the search runs, and pass it to the builder:

```php
use Foxws\ScoutBuilder\AllowedFilter;
use Foxws\ScoutBuilder\AllowedSort;
use Foxws\ScoutBuilder\ScoutBuilder;

public function __invoke(SearchPostsRequest $request)
{
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

    return PostResource::collection($posts);
}
```

- `ScoutBuilder::for()` takes a model class, a model, or an existing Scout builder (`Post::search('x')->where(...)`) to keep its conditions.
- Other Scout builder methods (`get()`, `paginate()`, `where()`, ...) are forwarded.
- Return the paginator through an API Resource or as an Inertia prop.

### 4. Filters

- `exact`, `in`, `notIn`, `trashed` (`with` / `only`), `operator($name, FilterOperator::GreaterThan)`, `dynamicOperator` (`?filter[price]=gte:120`; tokens `eq`, `neq`/`ne`, `lt`, `lte`, `gt`, `gte`), `scope` (calls `scopeOfCategory` for `of_category`), `callback` and `custom` (a class implementing `Foxws\ScoutBuilder\Filters\Filter`).
- `exact` and operator filters take one value: `a,b` throws `InvalidFilterValue`. Use `in` for several, or `->delimiter('')` when the values contain commas.
- `in` only narrows a `whereIn()` that's already on the builder, and `notIn` adds to a `whereNotIn()`. Put scopes such as the user's teams on the builder before `allowedFilters()`. `callback` and `custom` filters that call these methods replace them, as Scout does.
- Modifiers: `->default()`, `->nullable()`, `->ignore(...)`, `->delimiter()`.

A `custom` filter can validate too, for a rule that belongs to a reusable filter. An invalid value gives a `422`:

```php
use Foxws\ScoutBuilder\Filters\Filter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Scout\Builder;

class FiltersStatus implements Filter
{
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        Validator::make(
            ['filter' => [$property => $value]],
            ["filter.{$property}" => ['required', 'string', Rule::in(['draft', 'published'])]],
        )->validate();

        $query->where($property, $value);
    }
}

// In the allowedFilters() call:
AllowedFilter::custom('status', new FiltersStatus)->nullable();
```

- Add `->nullable()` and `required`: otherwise an empty value or `null` skips the filter or its rules, and drops its default.
- The value is already cast and split: use `integer` for `42`, `boolean` for `true`, and `array` for `a,b`. Ignored values never reach the filter. Validate in the `FormRequest` when the exact string matters.

### 5. Sorts

- `field`, `latest` / `oldest` (public name and column), `callback` and `custom` (implements `Foxws\ScoutBuilder\Sorts\Sort`); plain strings are `field` shorthand.
- `defaultSort()` / `defaultSorts()` apply only without a `sort` parameter; `->defaultDescending()` flips a sort's default direction.

### 6. Includes

`relationship` (`with()`), `count` (`withCount()`), `callback` and `custom` (implements `Foxws\ScoutBuilder\Includes\Includable`); plain strings are `relationship` shorthand.

### 7. Paginate

`jsonPaginate()` (with total) and `jsonSimplePaginate()` (next/previous only, cheaper) read `page[number]` and `page[size]`; override per call with `jsonPaginate(maxResults: 100, defaultSize: 50)`. Scout has no cursor pagination.

## Rules

- Validate every filter value in a `FormRequest` or its custom filter, with `Rule::in()` or a strict pattern: `string` alone lets through engine filter syntax, which Scout can't always quote. An `in` or `notIn` value is validated as the whole comma-separated string.
- Validation isn't authorization: constrain the builder to what the user may search, whatever filters are allowed.
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
