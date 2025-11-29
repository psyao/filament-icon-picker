<?php

declare(strict_types=1);

namespace Psyao\IconPicker\Forms;

use BladeUI\Icons\Factory;
use BladeUI\Icons\IconsManifest;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\IconSize;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class IconPickerTable
{
    /**
     * Cached flattened records built from the BladeUI icons manifest for this request.
     *
     * @var array<int, array<string, mixed>>|null
     */
    private static ?array $flatRecords = null;

    public static function configure(Table $table): Table
    {
        $sets = app(Factory::class)->all();
        $manifest = app(IconsManifest::class)->getManifest($sets);

        if (self::$flatRecords === null) {
            self::$flatRecords = self::buildFlatRecords($manifest, $sets);
        }

        return $table
            ->records(function (array $filters, ?string $sortColumn, ?string $sortDirection, ?string $search, int $page, int $recordsPerPage, Table $table) {
                $arguments = $table->getArguments();
                $selected = $arguments['selected'] ?? null;
                $allowedSets = $arguments['sets'] ?? null;

                // Start from the cached flattened list (defensive fallback to empty array).
                $records = collect(self::$flatRecords ?? []);

                // Filter early by allowed sets when provided.
                if (is_array($allowedSets) && count($allowedSets) > 0) {
                    $records = $records->whereIn('set', $allowedSets);
                }

                // If selected values exist, move them to the front.
                if (filled($selected)) {
                    $selectedIds = is_array($selected) ? $selected : [$selected];

                    [$before, $after] = $records->partition(fn (array $record) => in_array($record['id'], $selectedIds, true));

                    $records = $before->concat($after);
                }

                // Search against precomputed lowercase name for faster comparisons.
                if (filled($search)) {
                    $searchLower = Str::lower($search);

                    $records = $records->filter(fn (array $record): bool => str_contains($record['name_lower'], $searchLower));
                }

                if (filled($sortColumn)) {
                    $records = $records->sortBy($sortColumn, SORT_REGULAR, $sortDirection === 'desc');
                }

                if (filled($set = $filters['set']['value'] ?? null)) {
                    $records = $records->where('set', $set);
                }

                $keyedRecords = $records->mapWithKeys(fn (array $record) => [$record['id'] => $record]);

                return new LengthAwarePaginator(
                    $keyedRecords->forPage($page, $recordsPerPage),
                    total: $keyedRecords->count(),
                    perPage: $recordsPerPage,
                    currentPage: $page,
                );
            })
            ->columns([
                Split::make([
                    Stack::make([
                        IconColumn::make('id')
                            ->icon(fn ($state) => $state)
                            ->size(IconSize::TwoExtraLarge)
                            ->boolean(false)
                            ->tooltip(fn ($record) => $record['name'])
                            ->grow(false),
                    ])->alignment(Alignment::Center),
                ]),
            ])
            ->filters([
                SelectFilter::make('set')
                    ->options(
                        fn (Table $table) => collect($sets)
                            ->when(
                                is_array($allowedSets = $table->getArguments()['sets'] ?? null) && count($allowedSets) > 0,
                                fn (Collection $sets): Collection => $sets->filter(fn ($_, $key) => in_array($key, $allowedSets))
                            )
                            ->mapWithKeys(
                                fn ($set, $key) => [$key => str($key)->headline()->toString()]
                            )
                    ),
            ])
            ->searchable()
            ->paginated([
                10,
                25,
                50,
                100,
            ])
            ->defaultPaginationPageOption(50)
            ->extremePaginationLinks()
            ->paginationMode(PaginationMode::Default)
            ->contentGrid([
                'sm' => 4,
                'md' => 6,
                'lg' => 8,
                'xl' => 10,
            ]);
    }

    /**
     * Build a flattened array of icon records from the BladeUI manifest.
     *
     * @param  array<mixed>  $manifest
     * @param  array<string, array<string, mixed>>  $sets
     * @return array<int, array<string, mixed>>
     */
    private static function buildFlatRecords(array $manifest, array $sets): array
    {
        $records = [];

        foreach ($manifest as $setName => $setGroups) {
            // Ensure the manifest entry is iterable.
            if (! is_iterable($setGroups)) {
                continue;
            }

            $prefix = $sets[$setName]['prefix'] ?? $setName;

            foreach ($setGroups as $group) {
                if (! is_iterable($group)) {
                    continue;
                }

                foreach ($group as $icon) {
                    if (! is_scalar($icon)) {
                        continue;
                    }

                    $iconName = (string) $icon;

                    if ($iconName === '') {
                        continue;
                    }

                    $records[] = [
                        'id' => "{$prefix}-{$iconName}",
                        'name' => $iconName,
                        'name_lower' => Str::lower($iconName),
                        'set' => $setName,
                    ];
                }
            }
        }

        return $records;
    }
}
