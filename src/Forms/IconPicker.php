<?php

declare(strict_types=1);

namespace Psyao\FilamentIconPicker\Forms;

use Closure;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\ModalTableSelect;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Icon;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use LogicException;

class IconPicker extends ModalTableSelect
{
    /** @var array<int, string>|Closure|null */
    protected array | Closure | null $sets = [];

    protected Closure | bool $showIconLabels = false;

    /**
     * Set allowed sets. Accepts a Closure or array.
     *
     * @return $this
     */
    public function sets(Closure | array $allowedSets): static
    {
        $this->sets = $allowedSets;

        return $this;
    }

    /**
     * Set whether to show labels. Accepts a Closure or bool.
     *
     * @return $this
     */
    public function showIconLabels(Closure | bool $showIconLabels = true): static
    {
        $this->showIconLabels = $showIconLabels;

        return $this;
    }

    /**
     * Get evaluated sets as an array.
     *
     * @return array<int, string>
     */
    public function getSets(): array
    {
        $value = $this->evaluate($this->sets);

        if (! is_array($value)) {
            return [];
        }

        return $value;
    }

    public function getShowIconLabels(): bool
    {
        $value = $this->evaluate($this->showIconLabels);

        if (! is_bool($value)) {
            return false;
        }

        return $value;
    }

    protected function setUp(): void
    {
        parent::setUp();

        parent::beforeContent(function ($state) {
            if (is_array($state)) {
                $state = $state[0] ?? null;
            }

            if (! $state || ! is_string($state)) {
                return null;
            }

            return new HtmlString(Icon::make($state)->color('secondary')->toHtml());
        });

        parent::getOptionLabelUsing(fn ($value) => is_callable($value) ? $value() : $value);

        $this->selectAction(function (Action $action) {
            $action->modalWidth(Width::SevenExtraLarge);
        });

        // Assign protected properties directly instead of calling the setters so we
        // can override and block external calls to those methods.
        $this->tableConfiguration = IconsTable::class;

        $this->tableArguments = fn (Get $get): array => [
            'selected' => $get($this->getName()),
            'sets' => $this->getSets(),
            'showIconLabels' => $this->getShowIconLabels(),
        ];
    }

    /**
     * Prevent external callers (other schemas/pages) from invoking this method.
     */
    final public function beforeContent(array | Schema | Component | Action | ActionGroup | string | Htmlable | Closure | null $components): static
    {
        throw new LogicException('Calling beforeContent() on IconPicker is not allowed. Use the built-in icon preview behavior instead.');
    }

    /**
     * Prevent external callers from overriding the option label callback.
     */
    final public function getOptionLabelUsing(?Closure $callback): static
    {
        throw new LogicException('Calling getOptionLabelUsing() on IconPicker is not allowed.');
    }

    /**
     * Prevent external callers from setting a custom table configuration.
     */
    final public function tableConfiguration(string | Closure | null $tableConfiguration): static
    {
        throw new LogicException('Calling tableConfiguration() on IconPicker is not allowed.');
    }

    /**
     * Prevent external callers from overriding the table arguments.
     */
    final public function tableArguments(array | Closure $arguments): static
    {
        throw new LogicException('Calling tableArguments() on IconPicker is not allowed.');
    }
}
