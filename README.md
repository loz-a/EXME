# EXME Library

EXME - Extended Markup Expressions. A PHP library designed to provide a simple and extensible foundation for using template components inspired by PHP RFC: Native Markup Expressions

## Requirements

* PHP 8.5 or higher
* Composer

## Installation

Install the library using Composer:

```bash
composer require loz/exme
```

Composer will automatically configure PSR-4 autoloading for the library.

## Usage

After installing the package, include Composer's autoloader:

```php
require __DIR__ . '/vendor/autoload.php';
```

You can then use the library classes:

```php
<?php

use EXME\Example;

$example = new Example();
```

## Development

Clone the repository:

```bash
git clone https://github.com/andriy/my-library.git
cd my-library
```

Install development dependencies:

```bash
composer install
```

## Running Tests

Run the test suite with PHPUnit:

```bash
vendor/bin/phpunit
```

## Project Structure

```text
my-library/
├── src/                # Library source code
├── tests/              # PHPUnit tests
├── composer.json       # Composer package definition
├── phpunit.xml         # PHPUnit configuration
├── README.md           # Documentation
├── LICENSE             # MIT License
└── .gitignore          # Git exclusions
```

## License

This project is licensed under the MIT License.

See the [LICENSE](LICENSE) file for the full license text.
