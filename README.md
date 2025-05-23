# 🌐 Zephyr Shared Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/zephyr-it/shared.svg?style=flat-square)](https://packagist.org/packages/zephyr-it/shared)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/zephyr-it/shared/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/zephyr-it/shared/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/zephyr-it/shared/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/zephyr-it/shared/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/zephyr-it/shared.svg?style=flat-square)](https://packagist.org/packages/zephyr-it/shared)

Shared traits, report pages, Filament plugins, utilities, and helpers used across all Zephyr-IT modular Laravel packages.

This package provides:

-   A reusable `BasePlugin` system for Filament v3 auto-registration
-   Pre-built Filament resources (like Country, State, City)
-   Report export base classes for Laravel Excel
-   Language generation tooling (`scripts/check-lang.php`)
-   Helpful traits, support classes, and helpers

---

## 📦 Installation

Install via Composer:

```bash
composer require zephyr-it/shared
```

Optional publishable assets:

```bash
php artisan vendor:publish --tag="shared-config"
php artisan vendor:publish --tag="shared-migrations"
php artisan vendor:publish --tag="shared-views"
```

---

## 🧪 Usage

### ✳️ Registering the Filament Plugin

```php
use ZephyrIt\Shared\Filament\SharedPlugin;

Filament::plugin(SharedPlugin::make());
```

### 🛠 Running the Language Key Generator

From any Zephyr module:

```bash
composer lang:check
```

This will scan `src` and `resources/views` for translation keys and auto-generate structured language files.

---

## 🧬 Example Resources Included

This package ships with fully functional Filament resources:

-   `CountryResource`
-   `StateResource`
-   `CityResource`

These can be extended or used as-is in any Zephyr-IT module.

---

## 🧪 Testing

```bash
composer test
```

---

## 📄 Changelog

Please see [CHANGELOG](CHANGELOG.md) for details.

---

## 🤝 Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

---

## 🔐 Security

See [our security policy](../../security/policy).

---

## 🧠 Credits

-   [abbasmashaddy72](https://github.com/abbasmashaddy72)
-   [Zephyr-IT Team](https://github.com/zephyr-it)
-   [All Contributors](../../contributors)

---

## 📜 License

The MIT License (MIT). See [LICENSE.md](LICENSE.md) for more.

```

```
