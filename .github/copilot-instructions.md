<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4.14
- inertiajs/inertia-laravel (INERTIA) - v2.0.10
- laravel/fortify (FORTIFY) - v1.31.2
- laravel/framework (LARAVEL) - v12.36.1
- laravel/horizon (HORIZON) - v5.38.0
- laravel/octane (OCTANE) - v2.13.1
- laravel/prompts (PROMPTS) - v0.3.7
- laravel/reverb (REVERB) - v1.6.0
- laravel/sanctum (SANCTUM) - v4.2.0
- laravel/scout (SCOUT) - v10.20.0
- laravel/wayfinder (WAYFINDER) - v0.1.12
- larastan/larastan (LARASTAN) - v3.8.0
- laravel/envoy (ENVOY) - v2.10.2
- laravel/mcp (MCP) - v0.3.2
- laravel/pint (PINT) - v1.25.1
- laravel/telescope (TELESCOPE) - v5.15.0
- pestphp/pest (PEST) - v4.1.3
- phpunit/phpunit (PHPUNIT) - v12.4.1
- @inertiajs/vue3 (INERTIA) - v2.2.15
- @laravel/vite-plugin-wayfinder (WAYFINDER) - v0.1.7
- laravel-echo (ECHO) - v2.2.4
- tailwindcss (TAILWINDCSS) - v4.1.16
- vue (VUE) - v3.5.22
- eslint (ESLINT) - v9.39.0
- prettier (PRETTIER) - v3.6.2

## Domain-Driven Design Architecture

This application follows a **Domain-Driven Design (DDD)** architecture based on **Spatie's Laravel Beyond CRUD** principles. The architecture uses the following layer structure:

### `src/Domain` - Domain Layer

- Contains core business logic and entities
- Each domain (Users, Videos, Playlists, Groups, Tags, Media, etc.) includes:
    - `Models/` - Eloquent models representing domain entities
    - `Actions/` - Business logic and domain operations
    - `Jobs/` - Queued domain jobs
    - `Commands/` - Artisan commands specific to the domain
    - `States/` - Model states (using Spatie Model States)
    - `Policies/` - Authorization policies
    - `QueryBuilders/` - Custom Eloquent query builders for domain models
    - `Scopes/` - Query scopes for reusable query logic
    - `Collections/` - Custom Eloquent collection classes for enhanced model collection behavior
    - `Resources/` - API resource transformers for consistent data formatting
    - `Observers/` - Eloquent model observers for lifecycle event handling
    - `Enums/` - Domain-specific enumerations for type-safe constants and values
- Domain layer must remain framework-agnostic where possible
- Business rules and domain logic belong here

#### `src/Domain/Shared` - Shared Domain

- Contains domain logic that is shared across multiple domains
- Includes common value objects, traits, interfaces, and abstractions
- Should only contain truly cross-cutting domain concerns

### `src/App` - Application Layer

- HTTP layer containing controllers and requests
- `Api/` - API controllers and resources
- `Web/` - Web controllers for Inertia.js pages
- `Application.php` - Custom application class for framework bootstrapping
- Application services and use cases
- DTOs and data transformers
- Controllers should be thin - delegate to domain actions

### `src/Foundation` - Foundation Layer

- Service providers and framework bootstrapping
- Core infrastructure setup
- `Providers/` - Application service providers (e.g., `AppServiceProvider`, `FortifyServiceProvider`, `HorizonServiceProvider`, `TelescopeServiceProvider`)
- `Http/` - HTTP kernel and middleware configuration
- `Container/` - Container bindings and service container extensions
- `Helpers.php` - Global helper functions
- Examples: `AppServiceProvider`, `FortifyServiceProvider`, `HorizonServiceProvider`, `TelescopeServiceProvider`

### `src/Support` - Support Layer

- Cross-cutting concerns and shared utilities
- Framework extensions and custom implementations
- Helpers that don't belong to a specific domain
- `FFMpeg/` - Custom FFMpeg integration and video processing utilities
- `MediaLibrary/` - Spatie Media Library extensions and customizations
- `Inertia/` - Inertia.js framework extensions and middleware
- `Scout/` - Laravel Scout search engine customizations
- Examples: Custom FFMpeg, MediaLibrary, Inertia extensions

### Namespaces

All classes in `src/` follow PSR-4 autoloading with these base namespaces:

- `src/Domain/` → `Domain\` namespace
    - Example: `src/Domain/Users/Models/User.php` → `Domain\Users\Models\User`
    - Example: `src/Domain/Shared/Traits/HasUuid.php` → `Domain\Shared\Traits\HasUuid`
- `src/App/` → `App\` namespace
    - Example: `src/App/Web/Controllers/UserController.php` → `App\Web\Controllers\UserController`
    - Example: `src/App/Api/V1/Controllers/VideoController.php` → `App\Api\V1\Controllers\VideoController`
- `src/Foundation/` → `Foundation\` namespace
    - Example: `src/Foundation/Providers/AppServiceProvider.php` → `Foundation\Providers\AppServiceProvider`
- `src/Support/` → `Support\` namespace
    - Example: `src/Support/MediaLibrary/CustomPathGenerator.php` → `Support\MediaLibrary\CustomPathGenerator`

**Always use the correct namespace based on the file's location in the `src/` directory structure.**

### DDD Guidelines

- **Respect domain boundaries** - keep domains isolated and cohesive
- **Dependency direction** - outer layers depend on inner layers, never the reverse
- **Business logic placement** - domain logic belongs in `src/Domain`, not controllers
- **Naming conventions** - follow existing DDD naming patterns in sibling files
- **No new base directories** - do not create new top-level directories without approval
- When creating files, check sibling files in the same domain for structure and patterns

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure - don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Podman & Containerization

- This application uses **Podman** with **Quadlet** (systemd integration) for containerization in both development and production environments.
- Container configurations are stored in `containers/` directory with `Containerfile` for image builds.
- Quadlet service files are in `podman/systemd/` for systemd-managed container orchestration.
- The application runs in a **rootless** Podman setup for enhanced security.
- See `docs/podman.md` for detailed setup instructions and configuration.
- Use `bin/quadlet` helper script for common container management tasks.
- Container runtimes (FrankenPHP, etc.) are configured in `containers/runtimes/`.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `pnpm run build`, `pnpm run dev`, or `composer run dev`. Ask them.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

## Laravel Boost

- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan

- Use the `list-artisan-commands` tool when you need to call an Artisan command to double check the available parameters.

## URLs

- Whenever you share a project URL with the user you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain / IP, and port.

## Tinker / Debugging

- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool

- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)

- Boost comes with a powerful `search-docs` tool you should use before any other approaches. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation specific for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The 'search-docs' tool is perfect for all Laravel related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel-ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries - package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax

- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit"
3. Quoted Phrases (Exact Position) - query="infinite scroll" - Words must be adjacent and in that order
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit"
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms

=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors

- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function \_\_construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters.

### Type Declarations

- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments

- Prefer PHPDoc blocks over comments. Never use comments within the code itself unless there is something _very_ complex going on.

## PHPDoc Blocks

- Add useful array shape type definitions for arrays when appropriate.

## Enums

- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

## Facades vs Helper Functions

- **Always prefer Facades over helper functions** for better IDE support, static analysis, and consistency.
- Use `Auth::` instead of `auth()`
- Use `Cache::` instead of `cache()`
- Use `Collection::` instead of `collect()`
- Use `Config::` instead of `config()` (except in config files where `env()` is allowed)
- Use `DB::` only for complex raw queries; prefer Eloquent otherwise
- Use `Event::` instead of `event()`
- Use `Gate::` for authorization checks
- Use `Log::` instead of `logger()`
- Use `Request::` instead of `request()`
- Use `Route::` for routing operations
- Use `Storage::` instead of `storage_path()` for file operations
- Use `URL::` instead of `url()`
- Use `View::` instead of `view()`

<code-snippet name="Prefer Facades" lang="php">
// ✅ Good - Using Facades
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

$user = Auth::user();
$value = Cache::get('key');
Gate::authorize('update', $post);

// ❌ Avoid - Using helper functions
$user = auth()->user();
$value = cache('key');
gate()->authorize('update', $post);
</code-snippet>

=== inertia-laravel/core rules ===

## Inertia Core

- Inertia.js components should be placed in the `resources/js/Pages` directory unless specified differently in the JS bundler (vite.config.js).
- Use `Inertia::render()` for server-side routing instead of traditional Blade views.
- Use `search-docs` for accurate guidance on all things Inertia.

<code-snippet lang="php" name="Inertia::render Example">
// routes/web.php example
Route::get('/users', function () {
    return Inertia::render('Users/Index', [
        'users' => User::all()
    ]);
});
</code-snippet>

=== inertia-laravel/v2 rules ===

## Inertia v2

- Make use of all Inertia features from v1 & v2. Check the documentation before making any changes to ensure we are taking the correct approach.

### Inertia v2 New Features

- Polling
- Prefetching
- Deferred props
- Infinite scrolling using merging props and `WhenVisible`
- Lazy loading data on scroll

### Deferred Props & Empty States

- When using deferred props on the frontend, you should add a nice empty state with pulsing / animated skeleton.

### Inertia Form General Guidance

- The recommended way to build forms when using Inertia is with the `<Form>` component - a useful example is below. Use `search-docs` with a query of `form component` for guidance.
- Forms can also be built using the `useForm` helper for more programmatic control, or to follow existing conventions. Use `search-docs` with a query of `useForm helper` for guidance.
- `resetOnError`, `resetOnSuccess`, and `setDefaultsOnSuccess` are available on the `<Form>` component. Use `search-docs` with a query of 'form component resetting' for guidance.

=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database

- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation

- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues

- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization

- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration

- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] <name>` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `pnpm run build` or ask the user to run `pnpm run dev` or `composer run dev`.

=== laravel/v12 rules ===

## Laravel 12

- Use the `search-docs` tool to get version specific documentation.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

### Laravel 12 Structure

- No middleware files in `app/Http/Middleware/`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- **No app\Console\Kernel.php** - use `bootstrap/app.php` or `routes/console.php` for console configuration.
- **Commands auto-register** - files in `app/Console/Commands/` are automatically available and do not require manual registration.

### Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.

=== pest/core rules ===

## Pest

### Testing

- If you need to verify a feature is working, write or update a Unit / Feature test.

### Pest Tests

- All tests must be written using Pest. Use `php artisan make:test --pest <name>`.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files - these are core to the application.
- Tests should test all of the happy paths, failure paths, and weird paths.
- Tests live in the `tests/Feature` and `tests/Unit` directories.
- Pest tests look and behave like this:
  <code-snippet name="Basic Pest Test Example" lang="php">
  it('is true', function () {
  expect(true)->toBeTrue();
  });
  </code-snippet>

### Running Tests

- Run the minimal number of tests using an appropriate filter before finalizing code edits.
- To run all tests: `php artisan test`.
- To run all tests in a file: `php artisan test tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --filter=testName` (recommended after making a change to a related file).
- When the tests relating to your changes are passing, ask the user if they would like to run the entire test suite to ensure everything is still passing.

### Pest Assertions

- When asserting status codes on a response, use the specific method like `assertForbidden` and `assertNotFound` instead of using `assertStatus(403)` or similar, e.g.:
  <code-snippet name="Pest Example Asserting postJson Response" lang="php">
  it('returns all', function () {
  $response = $this->postJson('/api/docs', []);

                          $response->assertSuccessful();

    });
    </code-snippet>

### Mocking

- Mocking can be very helpful when appropriate.
- When mocking, you can use the `Pest\Laravel\mock` Pest function, but always import it via `use function Pest\Laravel\mock;` before using it. Alternatively, you can use `$this->mock()` if existing tests do.
- You can also create partial mocks using the same import or self method.

### Datasets

- Use datasets in Pest to simplify tests which have a lot of duplicated data. This is often the case when testing validation rules, so consider going with this solution when writing tests for validation rules.

<code-snippet name="Pest Dataset Example" lang="php">
it('has emails', function (string $email) {
    expect($email)->not->toBeEmpty();
})->with([
    'james' => 'james@laravel.com',
    'taylor' => 'taylor@laravel.com',
]);
</code-snippet>

=== pest/v4 rules ===

## Pest 4

- Pest v4 is a huge upgrade to Pest and offers: browser testing, smoke testing, visual regression testing, test sharding, and faster type coverage.
- Browser testing is incredibly powerful and useful for this project.
- Browser tests should live in `tests/Browser/`.
- Use the `search-docs` tool for detailed guidance on utilizing these features.

### Browser Testing

- You can use Laravel features like `Event::fake()`, `assertAuthenticated()`, and model factories within Pest v4 browser tests, as well as `RefreshDatabase` (when needed) to ensure a clean state for each test.
- Interact with the page (click, type, scroll, select, submit, drag-and-drop, touch gestures, etc.) when appropriate to complete the test.
- If requested, test on multiple browsers (Chrome, Firefox, Safari).
- If requested, test on different devices and viewports (like iPhone 14 Pro, tablets, or custom breakpoints).
- Switch color schemes (light/dark mode) when appropriate.
- Take screenshots or pause tests for debugging when appropriate.

### Example Tests

<code-snippet name="Pest Browser Test Example" lang="php">
it('may reset the password', function () {
    Notification::fake();

    $this->actingAs(User::factory()->create());

    $page = visit('/sign-in'); // Visit on a real browser...

    $page->assertSee('Sign In')
        ->assertNoJavascriptErrors() // or ->assertNoConsoleLogs()
        ->click('Forgot Password?')
        ->fill('email', 'nuno@laravel.com')
        ->click('Send Reset Link')
        ->assertSee('We have emailed your password reset link!')

    Notification::assertSent(ResetPassword::class);

});
</code-snippet>

<code-snippet name="Pest Smoke Testing Example" lang="php">
$pages = visit(['/', '/about', '/contact']);

$pages->assertNoJavascriptErrors()->assertNoConsoleLogs();
</code-snippet>

=== inertia-vue/core rules ===

## Inertia + Vue

- Vue components must have a single root element.
- Use `router.visit()` or `<Link>` for navigation instead of traditional links.

<code-snippet name="Inertia Client Navigation" lang="vue">

    import { Link } from '@inertiajs/vue3'
    <Link href="/">Home</Link>

</code-snippet>

### NuxtUI Components

- This application uses NuxtUI (`@nuxt/ui`) for UI components such as `UButton`, `UInput`, `UCard`, etc.
- Icons use Lucide via the `i-lucide-*` prefix (e.g., `i-lucide-user`, `i-lucide-clipboard-pen`, `i-lucide-eye`).
- Check existing components for NuxtUI usage patterns before creating new UI elements.

=== inertia-vue/v2/forms rules ===

## Inertia + Vue Forms

<code-snippet name="`<Form>` Component Example" lang="vue">

<Form
    action="/users"
    method="post"
    #default="{
        errors,
        hasErrors,
        processing,
        progress,
        wasSuccessful,
        recentlySuccessful,
        setError,
        clearErrors,
        resetAndClearErrors,
        defaults,
        isDirty,
        reset,
        submit,
  }"
>
    <input type="text" name="name" />

    <div v-if="errors.name">
        {{ errors.name }}
    </div>

    <button type="submit" :disabled="processing">
        {{ processing ? 'Creating...' : 'Create User' }}
    </button>

    <div v-if="wasSuccessful">User created successfully!</div>

</Form>

</code-snippet>

=== tailwindcss/core rules ===

## Tailwind Core

- Use Tailwind CSS classes to style HTML, check and use existing tailwind conventions within the project before writing your own.
- Offer to extract repeated patterns into components that match the project's conventions (i.e. Blade, JSX, Vue, etc..)
- Think through class placement, order, priority, and defaults - remove redundant classes, add classes to parent or child carefully to limit repetition, group elements logically
- You can use the `search-docs` tool to get exact examples from the official documentation when needed.

### Spacing

- When listing items, use gap utilities for spacing, don't use margins.

                        <code-snippet name="Valid Flex Gap Spacing Example" lang="html">
                            <div class="flex gap-8">
                                <div>Superior</div>
                                <div>Michigan</div>
                                <div>Erie</div>
                            </div>
                        </code-snippet>

### Dark Mode

- If existing pages and components support dark mode, new pages and components must support dark mode in a similar way, typically using `dark:`.

=== tailwindcss/v4 rules ===

## Tailwind 4

- Always use Tailwind CSS v4 - do not use the deprecated utilities.
- `corePlugins` is not supported in Tailwind v4.
- In Tailwind v4, you import Tailwind using a regular CSS `@import` statement, not using the `@tailwind` directives used in v3:

<code-snippet name="Tailwind v4 Import Tailwind Diff" lang="diff">
   - @tailwind base;
   - @tailwind components;
   - @tailwind utilities;
   + @import "tailwindcss";
</code-snippet>

### Replaced Utilities

- Tailwind v4 removed deprecated utilities. Do not use the deprecated option - use the replacement.
- Opacity values are still numeric.

| Deprecated | Replacement |
|------------+--------------|
| bg-opacity-_ | bg-black/_ |
| text-opacity-_ | text-black/_ |
| border-opacity-_ | border-black/_ |
| divide-opacity-_ | divide-black/_ |
| ring-opacity-_ | ring-black/_ |
| placeholder-opacity-_ | placeholder-black/_ |
| flex-shrink-_ | shrink-_ |
| flex-grow-_ | grow-_ |
| overflow-ellipsis | text-ellipsis |
| decoration-slice | box-decoration-slice |
| decoration-clone | box-decoration-clone |

=== laravel/fortify rules ===

## Laravel Fortify

Fortify is a headless authentication backend that provides authentication routes and controllers for Laravel applications.

**Before implementing any authentication features, use the `search-docs` tool to get the latest docs for that specific feature.**

### Configuration & Setup

- Check `config/fortify.php` to see what's enabled. Use `search-docs` for detailed information on specific features.
- Enable features by adding them to the `'features' => []` array: `Features::registration()`, `Features::resetPasswords()`, etc.
- To see the all Fortify registered routes, use the `list-routes` tool with the `only_vendor: true` and `action: "Fortify"` parameters.
- Fortify includes view routes by default (login, register). Set `'views' => false` in the configuration file to disable them if you're handling views yourself.

### Customization

- Views can be customized in `FortifyServiceProvider`'s `boot()` method using `Fortify::loginView()`, `Fortify::registerView()`, etc.
- Customize authentication logic with `Fortify::authenticateUsing()` for custom user retrieval / validation.
- Actions in `app/Actions/Fortify/` handle business logic (user creation, password reset, etc.). They're fully customizable, so you can modify them to change feature behavior.

## Available Features

- `Features::registration()` for user registration.
- `Features::emailVerification()` to verify new user emails.
- `Features::twoFactorAuthentication()` for 2FA with QR codes and recovery codes.
    - Add options: `['confirmPassword' => true, 'confirm' => true]` to require password confirmation and OTP confirmation before enabling 2FA.
- `Features::updateProfileInformation()` to let users update their profile.
- `Features::updatePasswords()` to let users change their passwords.
- `Features::resetPasswords()` for password reset via email.
  </laravel-boost-guidelines>
