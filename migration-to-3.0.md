# Migrating to v3.0 (Laravel 13 support)

## What changed

* **PHP 8.2+** is now required.
* **Laravel 8+** is now required (5/6/7 dropped — all EOL).
* `prettus/laravel-validation` is no longer a composer dependency. Its source
  is now shipped directly inside this package under the same `Prettus\Validator`
  namespace, so existing consumer code referencing `Prettus\Validator\Contracts\ValidatorInterface`
  or `Prettus\Validator\LaravelValidator` continues to work without modification.
* `CacheableRepository::getCacheTime()` no longer reads `$this->app->version()`
  to decide between minutes and seconds — it always returns seconds. If you
  were targeting Laravel 5.7 or earlier you will need to multiply your
  `$cacheMinutes` value before upgrading.
* The `ComparesVersionsTrait` regex now correctly parses Laravel/Lumen 10+
  version strings. If you were depending on the prior broken behavior (the
  trait silently fell through for multi-digit Laravel versions), update your
  callers — they will now receive accurate version comparisons.

## Action items

1. **Bump PHP and Laravel.** Make sure your application is on PHP 8.2+ and
   Laravel 8+ before upgrading this package.
2. **Remove the `prettus/laravel-validation` require** from your own
   `composer.json` if you only consumed it transitively through this package.
   If you used it directly elsewhere in your application, leave it.
3. **Whitelist criterion classes for the cache.** Laravel 13 ships with
   `cache.serializable_classes` hardened to `false` by default. If you use
   the `CacheableRepository` trait, update your `config/cache.php`:

   ```php
   'serializable_classes' => [
       Illuminate\Support\Collection::class,
       Illuminate\Pagination\LengthAwarePaginator::class,
       App\Repositories\Criteria\YourCriterion::class,
       App\Models\YourEloquentModel::class,
   ],
   ```

   Without this, cached criteria collections will fail to unserialize on cache
   read. Add every concrete model and criterion class that flows through your
   repositories.
4. **Re-run your test suite.** This package now bundles its own `Prettus\Validator`
   source — re-resolve composer autoload (`composer dump-autoload`) and confirm
   nothing in your app was relying on a `prettus/laravel-validation` autoload
   that you removed.

## What did not change

* The public API surface of `BaseRepository`, `RequestCriteria`,
  `CacheableRepository`, `FractalPresenter`, the generator commands, and all
  contracts is unchanged.
* Namespaces and class names are unchanged.
* The Laravel service provider FQN (`Prettus\Repository\Providers\RepositoryServiceProvider`)
  is unchanged.
