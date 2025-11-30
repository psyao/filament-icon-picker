# Filament Icon Picker

[![Latest Version on Packagist](https://img.shields.io/packagist/v/psyao/filament-icon-picker.svg?style=flat-square)](https://packagist.org/packages/psyao/filament-icon-picker)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/psyao/filament-icon-picker/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/psyao/filament-icon-picker/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/psyao/filament-icon-picker/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/psyao/filament-icon-picker/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/psyao/filament-icon-picker.svg?style=flat-square)](https://packagist.org/packages/psyao/filament-icon-picker)

This package provides an `IconPicker` form field for [Filament](https://filamentphp.com/) that allows users to select
icons from the collections registered with [Blade Icons](https://blade-ui-kit.com/docs/icons/introduction). By default,
heroicons are supported, but you can easily add more icon sets by registering them with Blade Icons.

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

Basic example:

```php
use Psyao\FilamentIconPicker\Forms\IconPicker;
use Filament\Forms\Form;

// In a resource or form component
public static function form(Form $form): Form
{
    return $form->schema([
        IconPicker::make('icon')
            ->label('Icon')
            ->required(),
    ]);
}
```

Filtering available icon sets:

```php
use Psyao\FilamentIconPicker\Forms\IconPicker;
use Filament\Forms\Form;

// In a resource or form component
public static function form(Form $form): Form
{
    return $form->schema([
        IconPicker::make('icon')
            ->label('Icon')
            ->sets(['lucide', 'simple-icons'])
            ->required(),
    ]);
}
```

Displaying labeled icons:

```php
use Psyao\FilamentIconPicker\Forms\IconPicker;
use Filament\Forms\Form;

// In a resource or form component
public static function form(Form $form): Form
{
    return $form->schema([
        IconPicker::make('icon')
            ->label('Icon')
            ->showIconLabels()
            ->required(),
    ]);
}
```

You can render the selected icon in your Blade views like this:

```blade
@svg($icon, ['class' => 'size-5']) 
``` 

or

```blade
{{ svg($icon, ['class' => 'size-5']) }}
``` 

Notes:

- The 'IconPicker' overrides the 'ModalTableSelect' field from Filament.
- Only use methods provided by this package (via the `IconPicker` field). Using the
  default `ModalTableSelect` methods may break selection behavior, styling, or live preview functionality.

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
