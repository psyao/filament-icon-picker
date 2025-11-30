# Filament Icon Picker

[![Latest Version on Packagist](https://img.shields.io/packagist/v/psyao/filament-icon-picker.svg?style=flat-square)](https://packagist.org/packages/psyao/filament-icon-picker)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/psyao/filament-icon-picker/run-tests.yml?branch=master&label=tests&style=flat-square)](https://github.com/psyao/filament-icon-picker/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/psyao/filament-icon-picker/fix-php-code-style-issues.yml?branch=master&label=code%20style&style=flat-square)](https://github.com/psyao/filament-icon-picker/actions?query=workflow%3A%22Fix+PHP+code+styling%22+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/psyao/filament-icon-picker.svg?style=flat-square)](https://packagist.org/packages/psyao/filament-icon-picker)

A Filament form field that provides a searchable icon picker backed
by [Blade UI Icons](https://blade-ui-kit.com/docs/icons/introduction).

By default, [Filament](https://filamentphp.com/docs/4.x/styling/icons) includes Heroicons out of the box. This package
makes it easy to surface additional icon sets (registered with Blade Icons) in a modal picker.

Notes

- `IconPicker` extends Filament's `ModalTableSelect` and customizes selection and preview behavior.
- Prefer the `IconPicker` API for configuration and usage — calling `ModalTableSelect` methods directly may break
  selection, styling, or the live preview.

Table of contents

- [Requirements](#requirements)
- [Features](#features)
- [Installation](#installation)
- [Usage](#usage)
    - [Forms](#in-forms)
    - [Tables](#in-tables)
    - [Infolists](#in-infolists)
    - [Blade views](#in-blade-views)
- [Testing](#testing)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [Security Vulnerabilities](#security-vulnerabilities)
- [Credits](#credits)
- [License](#license)

## Requirements

- PHP ^8.2
- Filament 4.x

## Features

- Modal picker with a searchable table of icons
- Live preview of the selected icon
- Filter available icon sets
- Simple integration with Filament forms, tables and infolists

## Installation

Install via Composer:

```bash
composer require psyao/filament-icon-picker
```

## Usage

### In forms

Use the `IconPicker` field inside your Filament form schema. The field opens a modal table that lets users pick an icon
and renders a live preview automatically.

Basic example:

```php
use Psyao\FilamentIconPicker\Forms\IconPicker;

IconPicker::make('icon')
```

Limit available sets:

```php
IconPicker::make('icon')
    ->sets(['lucide', 'simple-icons'])
```

Show labels under icons in the picker:

```php
IconPicker::make('icon')
    ->showIconLabels()
```

### In tables

To display the selected icon in a Filament table, use `IconColumn`:

```php
use Filament\Tables\Columns\IconColumn;

IconColumn::make('icon')
    ->icon(fn ($state) => $state)
```

### In infolists

To display the selected icon in an infolist, use `IconEntry`:

```php
use Filament\Infolists\Components\IconEntry;

IconEntry::make('status')
    ->icon(fn ($state) => $state)
```

### In Blade views

Render the selected icon in Blade (depending on your Blade Icons helper):

```blade
@svg($icon, ['class' => 'h-5 w-5'])

{{ svg($icon, ['class' => 'h-5 w-5']) }}
```

## Testing

Run the test suite with:

```bash
composer test
```

## Changelog

See [CHANGELOG](CHANGELOG.md) for recent changes.

## Contributing

See [CONTRIBUTING](.github/CONTRIBUTING.md) for contribution guidelines.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) for reporting vulnerabilities.

## Credits

- [Steve Aguet](https://github.com/psyao)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). See [LICENSE.md](LICENSE.md) for details.
