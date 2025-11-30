<?php

namespace Psyao\FilamentIconPicker;

use Filament\Contracts\Plugin;
use Filament\Panel;

class IconPickerPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-icon-picker';
    }

    public function register(Panel $panel): void
    {
        //
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        /** @var static $instance */
        $instance = app(static::class);

        return $instance;
    }

    public static function get(): static
    {
        /** @var static $instance */
        $instance = app(static::class);

        // filament() returns the plugin instance associated with the given id.
        /** @var static $plugin */
        $plugin = filament($instance->getId());

        return $plugin;
    }
}
