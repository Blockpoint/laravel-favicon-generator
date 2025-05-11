# Package to generate favicons within your Laravel application.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/blockpoint/laravel-favicon-generator.svg?style=flat-square)](https://packagist.org/packages/blockpoint/laravel-favicon-generator)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/blockpoint/laravel-favicon-generator/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/blockpoint/laravel-favicon-generator/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/blockpoint/laravel-favicon-generator/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/blockpoint/laravel-favicon-generator/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/blockpoint/laravel-favicon-generator.svg?style=flat-square)](https://packagist.org/packages/blockpoint/laravel-favicon-generator)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require blockpoint/laravel-favicon-generator
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laravel-favicon-generator-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-favicon-generator-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="laravel-favicon-generator-views"
```

## Usage

```php
$laravelFaviconGenerator = new Blockpoint\LaravelFaviconGenerator();
echo $laravelFaviconGenerator->echoPhrase('Hello, Blockpoint!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Blockpoint](https://github.com/Blockpoint)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
