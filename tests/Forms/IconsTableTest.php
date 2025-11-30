<?php

use BladeUI\Icons\Factory as BladeIconFactory;
use BladeUI\Icons\IconsManifest as BladeIconsManifest;
use Filament\Tables\Contracts\HasTable as HasTableContract;
use Filament\Tables\Table as FilamentTable;
use Illuminate\Pagination\LengthAwarePaginator;
use Psyao\FilamentIconPicker\Forms\IconsTable;

it('builds records and respects selected ordering, search, and pagination', function () {
    // Bind BladeUI factory stub
    $this->app->instance(BladeIconFactory::class, new class
    {
        public function all(): array
        {
            return [
                'demo' => ['prefix' => 'demo'],
            ];
        }
    });

    // Bind IconsManifest stub
    $this->app->instance(BladeIconsManifest::class, new class
    {
        public function getManifest($sets): array
        {
            return [
                'demo' => [
                    ['home', 'user'],
                ],
            ];
        }
    });

    // Create a simple HasTable mock and a real Filament Table instance
    $livewire = $this->createMock(HasTableContract::class);
    $table = FilamentTable::make($livewire)
        ->arguments([
            'selected' => 'demo-home',
            'sets' => null,
            'showIconLabels' => false,
        ]);

    // Clear static cache so tests are independent
    $ref = new \ReflectionClass(IconsTable::class);
    $prop = $ref->getProperty('flatRecords');
    $prop->setAccessible(true);
    $prop->setValue(null);

    $table = IconsTable::configure($table);

    // Retrieve the records closure from the table and invoke it to get the paginator
    $callback = $table->getDataSource();
    $paginator = $callback([], null, null, null, 1, 50, $table);

    expect($paginator)->toBeInstanceOf(LengthAwarePaginator::class);
    expect($paginator->total())->toBe(2);

    $items = $paginator->items();
    $ids = array_map(fn ($r) => $r['id'], $items);
    $ids = array_values($ids);

    expect($ids)->toContain('demo-home');
    expect($ids)->toContain('demo-user');
    expect($ids[0])->toBe('demo-home');
});

it('skips malformed manifest entries and non-scalar icon entries', function () {
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
                // Non-iterable set (should be skipped)
                'badset' => 'not-iterable',
                // Set not present in factory all() (should be skipped)
                'missing' => [['one']],
                // Valid set but with mixed invalid entries
                'demo' => [
                    [null, '', 123, 'RealIcon'],
                ],
            ];
        }
    });

    $livewire = $this->createMock(HasTableContract::class);
    $table = FilamentTable::make($livewire)
        ->arguments([
            'selected' => null,
            'sets' => null,
            'showIconLabels' => false,
        ]);

    // Clear static cache so tests are independent
    $ref = new \ReflectionClass(IconsTable::class);
    $prop = $ref->getProperty('flatRecords');
    $prop->setAccessible(true);
    $prop->setValue(null);

    $table = IconsTable::configure($table);

    $callback = $table->getDataSource();

    // Search for 'real' should match 'RealIcon' only.
    $paginator = $callback([], null, null, 'real', 1, 50, $table);

    expect($paginator)->toBeInstanceOf(LengthAwarePaginator::class);
    expect($paginator->total())->toBe(1);

    $items = $paginator->items();
    $ids = array_map(fn ($r) => $r['id'], $items);
    $ids = array_values($ids);

    expect($ids)->toEqual(['demo-RealIcon']);
});
