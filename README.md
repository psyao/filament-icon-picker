# Filament Icon Picker

[![Latest Version on Packagist](https://img.shields.io/packagist/v/psyao/filament-icon-picker.svg?style=flat-square)](https://packagist.org/packages/psyao/filament-icon-picker)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/psyao/filament-icon-picker/run-tests.yml?branch=master&label=tests&style=flat-square)](https://github.com/psyao/filament-icon-picker/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/psyao/filament-icon-picker/fix-php-code-style-issues.yml?branch=master&label=code%20style&style=flat-square)](https://github.com/psyao/filament-icon-picker/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/psyao/filament-icon-picker.svg?style=flat-square)](https://packagist.org/packages/psyao/filament-icon-picker)

A Filament form field that provides a searchable icon picker backed
by [Blade UI Icons](https://blade-ui-kit.com/docs/icons/introduction).

By default, [Filament](https://filamentphp.com/docs/4.x/styling/icons) adds heroicons out of the box. You can
easily add more icon sets by registering them with Blade Icons.

Notes:

- The 'IconPicker' overrides the 'ModalTableSelect' field from Filament.
- Only use methods provided by this package (via the `IconPicker` field). Using the
  default `ModalTableSelect` methods may break selection behavior, styling, or live preview functionality.

## Features

- Icon picker modal with searchable table of icons.
- Live preview of selected icon.
- Support for filtering available icon sets.
- Easy integration with Filament forms.

## Installation

You can install the package via composer:

```bash
composer require psyao/filament-icon-picker
```

## Usage

Use the `IconPicker` field inside your Filament form schema. The field opens a modal table that lets users pick an icon
and renders a live preview automatically.

### In forms

Basic example:

```php
use Psyao\FilamentIconPicker\Forms\IconPicker;

IconPicker::make('icon')
```

To display only specific icon sets, use the `sets` method:

```php
use Psyao\FilamentIconPicker\Forms\IconPicker;

IconPicker::make('icon')
    ->sets(['lucide', 'simple-icons'])
```

To show labels below the icons in the picker, use the `showIconLabels` method:
Displaying labeled icons:

```php
use Psyao\FilamentIconPicker\Forms\IconPicker;

IconPicker::make('icon')
    ->showIconLabels()
```

### In tables

To display the selected icon in a table, use the Filament `IconColumn`:

```php
use Filament\Tables\Columns\IconColumn;

IconColumn::make('icon')
    ->icon(fn($state) => $state)
```

### In infolists

To display the selected icon in an infolist, use the Filament `IconListItem`:

```php
use Filament\Infolists\Components\IconEntry;

IconEntry::make('status')
    ->icon(fn($state) => $state)
```

### In blade views

You can render the selected icon in your Blade views like this:

```blade
@svg($icon, ['class' => 'size-5']) 

{{ svg($icon, ['class' => 'size-5']) }}
``` 

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Steve Aguet](https://github.com/psyao)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
