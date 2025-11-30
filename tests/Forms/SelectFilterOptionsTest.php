<?php

use BladeUI\Icons\Factory as BladeIconFactory;
use BladeUI\Icons\IconsManifest as BladeIconsManifest;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable as HasTableContract;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table as FilamentTable;
use Psyao\FilamentIconPicker\Forms\IconsTable;

it('provides select filter options based on available sets and allowed sets', function () {
    $this->app->instance(BladeIconFactory::class, new class
    {
        public function all(): array
        {
            return [
                'demo' => ['prefix' => 'demo'],
                'other' => ['prefix' => 'other'],
            ];
        }
    });

    $this->app->instance(BladeIconsManifest::class, new class
    {
        public function getManifest($sets): array
        {
            return [
                'demo' => [['home', 'user']],
                'other' => [['a', 'b']],
            ];
        }
    });

    $livewire = $this->createMock(HasTableContract::class);
    $table = FilamentTable::make($livewire)
        ->arguments([
            'selected' => null,
            'sets' => ['demo'], // only allow demo
            'showIconLabels' => false,
        ]);

    // Clear static cache
    IconsTable::clearFlatRecordsForTesting();

    $configured = IconsTable::configure($table);

    // Find the SelectFilter in configured filters
    $filters = $configured->getFilters();

    $select = null;
    foreach ($filters as $filter) {
        if ($filter instanceof SelectFilter && $filter->getName() === 'set') {
            $select = $filter;

            break;
        }
    }

    expect($select)->not->toBeNull();

    // Retrieve evaluated options directly
    $options = $select->getOptions();

    // Since allowed sets contained only 'demo', options should be limited to demo
    expect($options)->toBeArray();
    expect(array_keys($options))->toEqual(['demo']);
    expect($options['demo'])->toBeString();
});

it('provides all select filter options when sets is null', function () {
    $this->app->instance(BladeIconFactory::class, new class
    {
        public function all(): array
        {
            return [
                'demo' => ['prefix' => 'demo'],
                'other' => ['prefix' => 'other'],
            ];
        }
    });

    $this->app->instance(BladeIconsManifest::class, new class
    {
        public function getManifest($sets): array
        {
            return [
                'demo' => [['home', 'user']],
                'other' => [['a', 'b']],
            ];
        }
    });

    $livewire = $this->createMock(HasTableContract::class);
    $table = FilamentTable::make($livewire)
        ->arguments([
            'selected' => null,
            'sets' => null, // allow all
            'showIconLabels' => false,
        ]);

    // Clear static cache
    IconsTable::clearFlatRecordsForTesting();

    $configured = IconsTable::configure($table);

    $filters = $configured->getFilters();

    $select = collect($filters)->first(fn ($filter) => $filter instanceof SelectFilter && $filter->getName() === 'set');

    expect($select)->not->toBeNull();

    $options = $select->getOptions();

    expect($options)->toBeArray();
    expect(array_keys($options))->toEqualCanonicalizing(['demo', 'other']);
});

it('evaluates TextColumn visibility closure for showIconLabels', function () {
    $this->app->instance(BladeIconFactory::class, new class
    {
        public function all(): array
        {
            return [
                'demo' => ['prefix' => 'demo'],
            ];
        }
    });

    $this->app->instance(BladeIconsManifest::class, new class
    {
        public function getManifest($sets): array
        {
            return [
                'demo' => [['home', 'user']],
            ];
        }
    });

    $livewire = $this->createMock(HasTableContract::class);

    // First: showIconLabels = false
    $tableA = FilamentTable::make($livewire)
        ->arguments([
            'selected' => null,
            'sets' => null,
            'showIconLabels' => false,
        ]);

    IconsTable::clearFlatRecordsForTesting();

    $configuredA = IconsTable::configure($tableA);

    // Inspect configured columns and find TextColumn named 'name'
    $columns = $configuredA->getColumns();
    $textColumn = $columns['name'];

    // The visibility callback is evaluated via isVisible() in Filament; call evaluate on the closure via the column
    $isVisibleA = $textColumn->isVisible();

    expect($isVisibleA)->toBeFalse();

    // Now showIconLabels = true
    $tableB = FilamentTable::make($livewire)
        ->arguments([
            'selected' => null,
            'sets' => null,
            'showIconLabels' => true,
        ]);

    // Clear cache and reconfigure
    IconsTable::clearFlatRecordsForTesting();
    $configuredB = IconsTable::configure($tableB);

    $columnsB = $configuredB->getColumns();
    $textColumnB = $columnsB['name'];

    $isVisibleB = $textColumnB->isVisible();

    expect($isVisibleB)->toBeTrue();
});
