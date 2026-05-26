# Changelog

All notable changes to `prettus/l5-repository` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Laravel 13 (`illuminate/* ^13.0`) compatibility.
- Orchestra Testbench-based test harness with 21 tests covering version comparison,
  service provider boot, cache TTL, BaseRepository CRUD, RequestCriteria search/order,
  and CacheableRepository round-trip on SQLite.
- GitHub Actions CI matrix across PHP 8.2–8.4 and Laravel 11/12/13.
- Documentation for the L13 `cache.serializable_classes` hardening — consumers using
  the cacheable trait must whitelist their criterion classes.
- Vendored `Prettus\Validator` source directly into this package; no external
  `prettus/laravel-validation` dependency is required anymore.

### Changed

- **BREAKING:** Minimum PHP raised to **8.2**.
- **BREAKING:** Minimum Laravel raised to **8.0**; Laravel 5, 6, and 7 are no longer
  supported (all EOL).
- `phpunit.xml` rewritten to the PHPUnit 12 schema (`<source>`, `cacheDirectory`,
  `failOnWarning`, `failOnRisky`). The legacy PHPUnit 5-era attributes
  (`backupStaticAttributes`, `convertErrorsToExceptions`, etc.) were removed.
- `CacheableRepository::getCacheTime()` always returns seconds. The Laravel 5.7
  conditional branch was removed (the package now requires Laravel 8+).

### Fixed

- `ComparesVersionsTrait` regex pattern now matches multi-digit Laravel and Lumen
  version components (10+). The previous `(\d\.\d\.[\d|\*])` pattern silently
  failed on Laravel/Lumen 10 onward and contained a buggy character class
  `[\d|\*]` that matched a literal pipe.

### Removed

- Dependency on `prettus/laravel-validation` — its classes are now vendored under
  `src/Prettus/Validator/`. Consumer code using `Prettus\Validator\*` continues
  to work unchanged.
