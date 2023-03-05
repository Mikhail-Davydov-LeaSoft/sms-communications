# Laravel SMS Communications

## Installation

You can install the package via composer:

```bash
composer require Mikhail-Davydov-LeaSoft/sms-communications
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="sms-communications-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="sms-communications-config"
```

This is the contents of the published config file:

```php
return [
    // ToDo
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="sms-communications-views"
```

## Usage

```php
// ToDo
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Mikhail](https://github.com/Mikhail-Davydov-LeaSoft)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
