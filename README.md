# EXME Library

EXME - Expandable Markup Expressions. A PHP library designed to provide a simple and extensible foundation for using template components inspired by PHP RFC: Native Markup Expressions https://wiki.php.net/rfc/native_markup_expressions and preprocess-pre-phpx 
 - https://github.com/assertchris/preprocess-pre-phpx 
 - https://github.com/assertchris/preprocess-pre-phpx-html 
 - https://github.com/assertchris/preprocess-example-phpx 
 - https://github.com/assertchris/preprocess-example-phpx-live

## Requirements

* PHP 8.5 or higher
* Composer

## Installation

Install the library using Composer:

```bash
composer require loz-a/exme
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
composer test
```

### Debugging PHPUnit from the terminal

Xdebug is configured for port `9003`. In VS Code, start **Listen for Xdebug**
from the Run and Debug panel, set a breakpoint (or add `xdebug_break()`
temporarily), then run this in the integrated terminal:

```bash
composer test:debug
```

To debug only one test, pass PHPUnit arguments after `--`:

```bash
composer test:debug -- --filter testParsesComponentWithoutAttributes
```

The listener maps `/var/www/current` in the PHP environment to the opened
workspace, so breakpoints work when the tests run in the project container.

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
