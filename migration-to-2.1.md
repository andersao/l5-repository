# Laravel 5 Repositories

[![Analytics](https://ga-beacon.appspot.com/UA-61050740-1/l5-repository/migration-to-2.1)](https://packagist.org/packages/prettus/l5-repository)

Some changes between version 2.0 and 2.1

## Migrate from version 2.0 to 2.1

### Lumen Support

To use this package with Lumen , register the following service provider

```php
$app->register(Prettus\Repository\Providers\LumenRepositoryServiceProvider::class);
```

### Composer requirements

In version 2.0 some dependencies that could sometimes not be used in your project were always being downloaded regardless of whether they are used. In version 2.1 this package are as suggestions that will only be required if you want some features


#### [Validators](https://github.com/prettus/l5-repository#validators)

If you want to use validations directly to your repository, as is disbelieved in Section [Validators](https://github.com/prettus/l5-repository#validators) , you need to use the library `prettus/laravel-validator`.

`composer require prettus/laravel-validator`

#### [Presenters](https://github.com/prettus/l5-repository#presenters)

If you want to use presenters in your repository, you need to use the library [`league/fractal`](http://fractal.thephpleague.com/).

`composer require league/fractal`