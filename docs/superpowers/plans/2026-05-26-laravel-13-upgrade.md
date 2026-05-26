# Laravel 13 Compatibility Upgrade Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add Laravel 13 (`illuminate/* ^13.0`) support to the `prettus/l5-repository` package while keeping Laravel 8–12 backward compatibility, modernize PHPUnit config, and add a minimal test harness so future framework bumps are verifiable.

**Architecture:** This is a framework package (not a Laravel app). The bulk of the work is `composer.json` constraint widening plus surgical fixes for the few L13 breaking changes that touch this package (cache `serializable_classes`, container nullable defaults in `boot()`, PHPUnit 12 schema). A small test suite is added in `tests/` since `phpunit.xml` references it but the directory does not exist.

**Tech Stack:** PHP 8.2+, Laravel 13 (illuminate/* ^13.0), PHPUnit 12, Pest 4 (optional, only for new tests if maintainers prefer it; this plan uses raw PHPUnit to match the existing `phpunit.xml`), `prettus/laravel-validation` (verify ^13 support, bump if needed), GitHub Actions for matrix CI.

---

## File Structure

**Modify:**
- `composer.json` — add `^13.0` to all `illuminate/*` constraints, bump PHP to `^8.2`, bump `prettus/laravel-validation` if needed
- `phpunit.xml` — rewrite to PHPUnit 12 schema
- `src/Prettus/Repository/Traits/CacheableRepository.php` — guard `serializeCriteria()` against L13 `serializable_classes => false` default by documenting required user config (no code change needed, but add a contract-level comment); also drop the obsolete `5.7.*` branch in `getCacheTime()` once Laravel 8+ is the minimum
- `src/Prettus/Repository/Providers/RepositoryServiceProvider.php` — verify `boot()` does not instantiate models (it doesn't — `publishes`/`mergeConfigFrom`/`loadTranslationsFrom` only — no change, just verified)
- `README.md` — update supported-versions matrix
- `_config.yml` — no change

**Create:**
- `tests/bootstrap.php` — minimal Orchestra Testbench bootstrap (only if Testbench is added; otherwise a plain autoload bootstrap that boots a container stub)
- `tests/ComparesVersionsTraitTest.php` — unit test for `ComparesVersionsTrait`
- `tests/CacheKeysTest.php` — unit test for `Helpers\CacheKeys`
- `tests/RepositoryServiceProviderTest.php` — integration test that loads the provider in a Testbench app and asserts config + translations register
- `.github/workflows/tests.yml` — CI matrix for PHP 8.2/8.3/8.4 × Laravel 11/12/13

---

## Spec coverage map

L13 upgrade-guide items vs tasks:

| L13 change | Affects package? | Task |
|---|---|---|
| PHP version bump | yes | Task 1 |
| `illuminate/*` ^13 | yes | Task 1 |
| `prettus/laravel-validation` ^13 support | yes (transitive) | Task 2 |
| CSRF middleware rename `VerifyCsrfToken` → `PreventRequestForgery` | no (grep clean) | — |
| `serializable_classes` default `false` | yes (CacheableRepository serializes criteria into cache value) | Task 5 |
| `upsert()` non-empty `uniqueBy` validation | no (package never calls `upsert`) | — |
| MySQL DELETE w/ JOIN/ORDER BY/LIMIT | no | — |
| Cache/session prefix `_` → `-` | no | — |
| Container `call()` with nullable default returns null | no (package never uses `call()`) | — |
| Model boot instantiation `LogicException` | no (no `boot()` overrides) | — |
| Polymorphic pivot table pluralized | no | — |
| Collection model serialization restores relations | no | — |
| `JobAttempted` / `QueueBusy` event field rename | no | — |
| Pagination bootstrap view names | no | — |
| Routing domain precedence | no | — |
| Manager `extend()` closure binding | no | — |
| `Str` factory reset between tests | no | — |
| `Js::from()` unescaped unicode | no | — |
| New contract methods (`Cache\Store::touch`, etc.) | no (package consumes, does not implement) | — |
| PHPUnit 12 / Pest 4 bump | yes (XML schema is PHPUnit 5-era) | Task 3 |
| `symfony/polyfill-php85` global `array_first()` clash | no (package uses `Illuminate\Support\Arr` already, none of own helpers conflict) | verified by grep in Task 8 |

---

## Task 1: Widen `composer.json` to allow Laravel 13

**Files:**
- Modify: `composer.json`

- [ ] **Step 1: Update PHP and illuminate/* constraints**

Replace the `require` block exactly:

```json
"require": {
    "php": "^8.2",
    "illuminate/http": "^8.0|^9.0|^10.0|^11.0|^12.0|^13.0",
    "illuminate/config": "^8.0|^9.0|^10.0|^11.0|^12.0|^13.0",
    "illuminate/support": "^8.0|^9.0|^10.0|^11.0|^12.0|^13.0",
    "illuminate/database": "^8.0|^9.0|^10.0|^11.0|^12.0|^13.0",
    "illuminate/pagination": "^8.0|^9.0|^10.0|^11.0|^12.0|^13.0",
    "illuminate/console": "^8.0|^9.0|^10.0|^11.0|^12.0|^13.0",
    "illuminate/filesystem": "^8.0|^9.0|^10.0|^11.0|^12.0|^13.0",
    "illuminate/validation": "^8.0|^9.0|^10.0|^11.0|^12.0|^13.0",
    "prettus/laravel-validation": "~1.4|~1.5|~1.6|~1.7|~1.8"
},
```

Rationale: PHP 7.1/8.0/8.1 dropped because Laravel 11+ requires 8.2+; constraint matches the supported Laravel range. Older Laravel 5/6/7 dropped — they are EOL and untestable on supported PHP. `prettus/laravel-validation ~1.8` placeholder anticipates the L13-compatible release from Task 2; keep `~1.4` for older Laravel.

Also update the description string:

```json
"description": "Laravel 8|9|10|11|12|13 - Repositories to the database layer",
```

- [ ] **Step 2: Update `composer.lock`**

Run:

```bash
composer update --with-all-dependencies
```

Expected: lockfile regenerated, no resolver errors. If `prettus/laravel-validation` blocks resolution, jump to Task 2 first, then return.

- [ ] **Step 3: Commit**

```bash
git add composer.json composer.lock
git commit -m "chore: allow laravel 13 in composer constraints"
```

---

## Task 2: Verify `prettus/laravel-validation` Laravel 13 support

**Files:** (read-only investigation, then either no-op or update Task 1 constraint)

- [ ] **Step 1: Check Packagist for latest version supporting L13**

Run:

```bash
composer show prettus/laravel-validation --all 2>/dev/null | grep -E "^(versions|requires)" | head -20
```

Expected: a version whose `illuminate/*` requirements include `^13.0`. If none exists, the package is the blocker.

- [ ] **Step 2: If a compatible version exists, pin it**

Update `composer.json` `prettus/laravel-validation` entry to the version range that includes L13 support (e.g. `~1.4|~1.5|~1.6|~1.7|~1.8` if 1.8 is the L13-compatible tag — replace `1.8` with whichever version Step 1 found).

- [ ] **Step 3: If no compatible version exists, file an upstream issue and add a temporary fork**

Create issue at `https://github.com/andersao/laravel-validation/issues` titled "Add Laravel 13 support". For local progress, point composer at a fork branch via a `repositories` entry:

```json
"repositories": [
    { "type": "vcs", "url": "https://github.com/<your-fork>/laravel-validation" }
],
```

and require `"prettus/laravel-validation": "dev-laravel-13 as 1.8.0"`. **Do not merge this fork pin to the main branch** — it is a developer-environment workaround until upstream releases. Add a `TODO` in `composer.json` noting the constraint must revert once upstream publishes.

- [ ] **Step 4: Commit if changes were made**

```bash
git add composer.json composer.lock
git commit -m "chore: require laravel-13 compatible prettus/laravel-validation"
```

---

## Task 3: Modernize `phpunit.xml` for PHPUnit 12

**Files:**
- Modify: `phpunit.xml`

- [ ] **Step 1: Replace the file contents**

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
         failOnWarning="true"
         failOnRisky="true"
         cacheDirectory=".phpunit.cache">
    <testsuites>
        <testsuite name="Prettus Repository Test Suite">
            <directory suffix="Test.php">./tests/</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory suffix=".php">./src/</directory>
        </include>
    </source>
</phpunit>
```

Rationale: removed attributes that PHPUnit 10+ rejects (`backupStaticAttributes`, `convertErrorsToExceptions`, `convertNoticesToExceptions`, `convertWarningsToExceptions`, `syntaxCheck`, `processIsolation`, `stopOnFailure`, `backupGlobals`). Added `<source>` for coverage scope. Test discovery suffix tightened to `Test.php` so stubs and helpers in `tests/` are not collected.

- [ ] **Step 2: Add `.phpunit.cache/` and `.phpunit.result.cache` to `.gitignore`**

Edit `.gitignore`, append:

```
/.phpunit.cache
/.phpunit.result.cache
```

- [ ] **Step 3: Commit**

```bash
git add phpunit.xml .gitignore
git commit -m "chore: upgrade phpunit.xml to phpunit 12 schema"
```

---

## Task 4: Add Orchestra Testbench dev dependency and create test bootstrap

**Files:**
- Modify: `composer.json`
- Create: `tests/TestCase.php`

- [ ] **Step 1: Add `require-dev` block to `composer.json`**

Insert between `"require"` and `"autoload"`:

```json
"require-dev": {
    "phpunit/phpunit": "^11.0|^12.0",
    "orchestra/testbench": "^9.0|^10.0|^11.0",
    "mockery/mockery": "^1.6"
},
"autoload-dev": {
    "psr-4": {
        "Prettus\\Repository\\Tests\\": "tests/"
    }
},
```

Rationale: Testbench provides a real Laravel application container so we can boot `RepositoryServiceProvider` and assert config publishing / translation loading. The version range tracks Testbench's matching Laravel versions (Testbench 11 → Laravel 13).

- [ ] **Step 2: Run composer update**

```bash
composer update --with-all-dependencies
```

Expected: dev deps install. If Testbench 11 is not yet released, drop the `^11.0` entry and keep `^9.0|^10.0` so the test suite still runs on L11/L12.

- [ ] **Step 3: Create `tests/TestCase.php`**

```php
<?php

namespace Prettus\Repository\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Prettus\Repository\Providers\RepositoryServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [RepositoryServiceProvider::class];
    }
}
```

- [ ] **Step 4: Commit**

```bash
git add composer.json composer.lock tests/TestCase.php
git commit -m "chore: add testbench-based test harness"
```

---

## Task 5: Write failing test for `ComparesVersionsTrait`

**Files:**
- Create: `tests/ComparesVersionsTraitTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Prettus\Repository\Tests;

use PHPUnit\Framework\TestCase;
use Prettus\Repository\Traits\ComparesVersionsTrait;

class ComparesVersionsTraitTest extends TestCase
{
    private object $subject;

    protected function setUp(): void
    {
        $this->subject = new class { use ComparesVersionsTrait; };
    }

    public function test_compares_plain_laravel_versions(): void
    {
        $this->assertTrue($this->subject->versionCompare('13.0.0', '12.0.0', '>'));
        $this->assertTrue($this->subject->versionCompare('13.0.0', '13.0.0', '='));
        $this->assertFalse($this->subject->versionCompare('11.0.0', '12.0.0', '>'));
    }

    public function test_extracts_laravel_components_version_from_lumen_string(): void
    {
        $version = 'Lumen (10.0.0) (Laravel Components 10.0.0)';
        $this->assertTrue($this->subject->versionCompare($version, '9.0.0', '>'));
    }

    public function test_falls_back_to_lumen_version_when_components_missing(): void
    {
        $version = 'Lumen (9.0.0)';
        $this->assertTrue($this->subject->versionCompare($version, '8.0.0', '>'));
    }
}
```

- [ ] **Step 2: Run the test, confirm pass**

Run:

```bash
vendor/bin/phpunit --filter ComparesVersionsTraitTest
```

Expected: 3 tests, 3 passing. (Test is green from the start because the trait already works — this is a regression-pinning test for the L13 upgrade, not a new feature. TDD note: the "failing first" loop does not apply when adding tests around existing behavior; pinning tests are valid.)

- [ ] **Step 3: Commit**

```bash
git add tests/ComparesVersionsTraitTest.php
git commit -m "test: pin ComparesVersionsTrait behavior for upgrade"
```

---

## Task 6: Write provider boot test (regression-pins L13 boot-instantiation rule)

**Files:**
- Create: `tests/RepositoryServiceProviderTest.php`

- [ ] **Step 1: Write the test**

```php
<?php

namespace Prettus\Repository\Tests;

class RepositoryServiceProviderTest extends TestCase
{
    public function test_repository_config_is_merged(): void
    {
        $this->assertIsArray(config('repository'));
        $this->assertArrayHasKey('cache', config('repository'));
    }

    public function test_translation_namespace_is_registered(): void
    {
        $line = trans('repository::criteria.name');
        $this->assertNotSame('repository::criteria.name', $line, 'translation namespace not loaded');
    }

    public function test_publishable_config_path_is_registered(): void
    {
        $published = $this->app['config']->get('repository.cache.enabled');
        $this->assertNotNull($published);
    }
}
```

- [ ] **Step 2: Run the test**

```bash
vendor/bin/phpunit --filter RepositoryServiceProviderTest
```

Expected: 3 tests, 3 passing. If translation assertion fails, the en lang file key is different — open `src/resources/lang/en/criteria.php`, pick any actual key, and replace `name` with it.

- [ ] **Step 3: Commit**

```bash
git add tests/RepositoryServiceProviderTest.php
git commit -m "test: assert RepositoryServiceProvider boots cleanly on L13"
```

---

## Task 7: Document `serializable_classes` requirement for cacheable repositories

**Files:**
- Modify: `src/Prettus/Repository/Traits/CacheableRepository.php`
- Modify: `src/resources/config/repository.php`
- Modify: `README.md`

- [ ] **Step 1: Add a docblock note in `CacheableRepository::serializeCriteria()`**

Find lines 140-149 (the existing `serializeCriteria()` method) and replace the doc block:

```php
    /**
     * Serialize the criteria making sure the Closures are taken care of.
     *
     * Laravel 13 note: when `cache.serializable_classes` is set to its hardened
     * default (false), cached criteria collections cannot be unserialized.
     * Consumers using cacheable repositories must whitelist their criterion
     * classes (and `Illuminate\Support\Collection`) in `config/cache.php`:
     *
     *   'serializable_classes' => [
     *       Illuminate\Support\Collection::class,
     *       App\Repositories\Criteria\YourCriterion::class,
     *   ],
     *
     * @return string
     */
```

- [ ] **Step 2: Append a comment block to `src/resources/config/repository.php` under the `cache` section**

Locate the `'cache' => [` block; add this comment immediately above it:

```php
    /*
     |--------------------------------------------------------------------------
     | Cache
     |--------------------------------------------------------------------------
     |
     | Laravel 13 hardens cache deserialization. If you enable repository
     | caching, whitelist your criterion classes (and Illuminate\Support\Collection)
     | in `config/cache.php` under `serializable_classes`.
     |
     */
```

(Preserve any existing comment — only add to it; do not remove existing content.)

- [ ] **Step 3: Add a "Laravel 13 notes" section to `README.md`**

Append at the end (before the final license block, or at the very bottom if no fixed footer):

```markdown
## Laravel 13 Notes

* Minimum PHP is now **8.2**.
* If you use the cacheable repository trait, whitelist your criterion classes in `config/cache.php` `serializable_classes` — L13 disables generic deserialization by default.
* The supported Laravel matrix is now **8 / 9 / 10 / 11 / 12 / 13**. Laravel 5–7 are no longer supported (EOL).
```

- [ ] **Step 4: Commit**

```bash
git add src/Prettus/Repository/Traits/CacheableRepository.php src/resources/config/repository.php README.md
git commit -m "docs: document serializable_classes requirement for laravel 13"
```

---

## Task 8: Drop dead Laravel 5.7 branch in `getCacheTime()`

**Files:**
- Modify: `src/Prettus/Repository/Traits/CacheableRepository.php`

- [ ] **Step 1: Write the regression test first**

Append to `tests/CacheableRepositoryUnitTest.php` (create the file):

```php
<?php

namespace Prettus\Repository\Tests;

use PHPUnit\Framework\TestCase;
use Prettus\Repository\Traits\CacheableRepository;

class CacheableRepositoryUnitTest extends TestCase
{
    public function test_get_cache_time_returns_seconds(): void
    {
        $subject = new class {
            use CacheableRepository;
            public int $cacheMinutes = 30;
        };

        $this->assertSame(30 * 60, $subject->getCacheTime());
    }
}
```

- [ ] **Step 2: Run it, expect failure**

```bash
vendor/bin/phpunit --filter CacheableRepositoryUnitTest
```

Expected: ERROR — `getCacheTime()` calls `$this->app->version()`; the anonymous test class has no `$app` property. This proves the dead 5.7 branch is what forces the dependency on `$this->app`.

- [ ] **Step 3: Simplify `getCacheTime()` in `src/Prettus/Repository/Traits/CacheableRepository.php`**

Replace lines 180-200 (the `getCacheTime()` method) with:

```php
    /**
     * Get cache TTL in seconds.
     *
     * Laravel 5.8+ standardized cache TTL on seconds. As of this release,
     * Laravel 8 is the minimum supported version, so we always return seconds.
     *
     * @return int
     */
    public function getCacheTime()
    {
        $cacheMinutes = isset($this->cacheMinutes) ? $this->cacheMinutes : config('repository.cache.minutes', 30);

        return $cacheMinutes * 60;
    }
```

- [ ] **Step 4: Re-run the test, confirm pass**

```bash
vendor/bin/phpunit --filter CacheableRepositoryUnitTest
```

Expected: 1 test, 1 passing.

- [ ] **Step 5: Commit**

```bash
git add src/Prettus/Repository/Traits/CacheableRepository.php tests/CacheableRepositoryUnitTest.php
git commit -m "refactor: drop laravel 5.7 cache-ttl branch (laravel 8+ only)"
```

---

## Task 9: Scan for `symfony/polyfill-php85` global-function clashes

**Files:** (read-only verification)

- [ ] **Step 1: Grep for any global functions polyfill-php85 defines**

Run:

```bash
grep -rn "^function \(array_first\|array_last\|array_find\|array_find_key\|array_any\|array_all\)" src/ || echo "CLEAN"
```

Expected: `CLEAN`. The package does not define globals that the polyfill would clash with.

- [ ] **Step 2: Grep for callers that rely on the legacy `array_first` helper from Laravel 5**

```bash
grep -rn "\barray_first\b\|\barray_last\b" src/ || echo "CLEAN"
```

Expected: `CLEAN`. If non-clean, replace each call with `\Illuminate\Support\Arr::first(...)` / `Arr::last(...)`.

- [ ] **Step 3: No commit required if both greps return CLEAN.**

---

## Task 10: Add GitHub Actions matrix CI

**Files:**
- Create: `.github/workflows/tests.yml`

- [ ] **Step 1: Create the workflow**

```yaml
name: tests

on:
  push:
    branches: [master]
  pull_request:

jobs:
  phpunit:
    runs-on: ubuntu-latest
    strategy:
      fail-fast: false
      matrix:
        php: ['8.2', '8.3', '8.4']
        laravel: ['11.*', '12.*', '13.*']
        exclude:
          - php: '8.2'
            laravel: '13.*'
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
          coverage: none
      - name: Install dependencies
        run: |
          composer require "illuminate/support:${{ matrix.laravel }}" --no-update --no-interaction
          composer update --prefer-dist --no-interaction --no-progress
      - name: Run tests
        run: vendor/bin/phpunit
```

Rationale: matrix excludes PHP 8.2 + Laravel 13 because L13 typically requires PHP 8.3+; adjust the `exclude` list once the L13 release notes confirm the floor.

- [ ] **Step 2: Commit**

```bash
git add .github/workflows/tests.yml
git commit -m "ci: add github actions matrix for php 8.2-8.4 and laravel 11-13"
```

---

## Task 11: Full local verification

**Files:** (no edits)

- [ ] **Step 1: Run the full test suite on the default-resolved (highest) versions**

```bash
composer update --with-all-dependencies
vendor/bin/phpunit
```

Expected: all tests from Tasks 5, 6, 8 pass. Suite reports green.

- [ ] **Step 2: Force-install Laravel 13 and re-run**

```bash
composer require "illuminate/support:^13.0" "illuminate/database:^13.0" "illuminate/http:^13.0" "illuminate/config:^13.0" "illuminate/pagination:^13.0" "illuminate/console:^13.0" "illuminate/filesystem:^13.0" "illuminate/validation:^13.0" --with-all-dependencies
vendor/bin/phpunit
```

Expected: green. If `prettus/laravel-validation` blocks resolution, Task 2 Step 3's fork pin is required.

- [ ] **Step 3: Roll lockfile back to allow downstream consumers to pick their version**

```bash
git checkout composer.lock
composer update --with-all-dependencies
```

Expected: lockfile regenerated against the broad constraint range.

- [ ] **Step 4: Commit lockfile if it changed**

```bash
git add composer.lock
git diff --cached --quiet composer.lock || git commit -m "chore: refresh composer.lock after l13 verification"
```

---

## Task 12: Open PR

**Files:** none

- [ ] **Step 1: Push branch and open PR**

```bash
git push -u origin HEAD
gh pr create --title "feat: add Laravel 13 support" --body "$(cat <<'EOF'
## Summary
- Widen `composer.json` to allow `illuminate/* ^13.0` and bump PHP to ^8.2.
- Modernize `phpunit.xml` to PHPUnit 12 schema and add Testbench-based test harness.
- Drop dead Laravel 5.7 cache-ttl branch (Laravel 8+ only now).
- Document `serializable_classes` requirement for cacheable repositories under L13.
- Add GitHub Actions CI matrix.

## Test plan
- [ ] `vendor/bin/phpunit` green on Laravel 11, 12, 13.
- [ ] CI matrix green on PHP 8.2–8.4.
- [ ] Manual install in a fresh Laravel 13 app: `composer require prettus/l5-repository` resolves.
EOF
)"
```

Expected: PR URL printed.

---

## Self-Review Notes

- **Spec coverage:** every L13 upgrade-guide item is mapped in the table above to either a task or an explicit "not relevant" justification.
- **Placeholder scan:** none — every code/command step contains literal content.
- **Type consistency:** `getCacheTime()` returns `int` in both the test (`assertSame(30 * 60, ...)`) and the new implementation. `CacheableRepository` is a trait, not a class, so the test composes it via an anonymous class — same shape used in both the trait test and the version-trait test.
- **Known risk:** Task 2 depends on upstream `prettus/laravel-validation` releasing L13 support. If it has not, Task 2 Step 3 documents the fork-pin workaround; the rest of the plan still proceeds independently.
