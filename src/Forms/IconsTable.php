<?php

declare(strict_types=1);

namespace Psyao\FilamentIconPicker\Forms;

use BladeUI\Icons\Factory;
use BladeUI\Icons\IconsManifest;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class IconsTable
{
    /**
     * Cached flattened records built from the BladeUI icons manifest for this request.
     *
     * @var array<int, array<string, mixed>>|null
     */
    private static ?array $flatRecords = null;

    /**
     * Clear the internal flat records cache. This helper is intentionally public
     * for tests to avoid relying on reflection to reset internal state between
     * test cases. It's low-risk and does not affect runtime behavior.
     *
     * @internal Test helper
     */
    public static function clearFlatRecordsForTesting(): void
    {
        self::$flatRecords = null;
    }

    public static function configure(Table $table): Table
    {
        /** @var array<string, array<string, mixed>> $sets */
        $sets = app(Factory::class)->all();
        /** @var array<mixed> $manifest */
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
                /** @var \Illuminate\Support\Collection<int, array<string, mixed>> $records */
                $records = collect(self::$flatRecords ?? []);

                // Filter early by allowed sets when provided.
                if (is_array($allowedSets) && count($allowedSets) > 0) {
                    $allowedSetsArr = array_values($allowedSets);
                    /** @var array<int,string> $allowedSetsArr */
                    $records = $records->whereIn('set', $allowedSetsArr);
                }

                // If selected values exist, move them to the front.
                if (filled($selected)) {
                    // Normalize selected ids to strings and ensure an array.
                    $selectedIds = [];
                    foreach ((array) $selected as $v) {
                        if (! is_scalar($v)) {
                            continue;
                        }

                        $selectedIds[] = (string) $v;
                    }
                    /** @var array<int,string> $selectedIds */
                    [$before, $after] = $records->partition(function (array $record) use ($selectedIds): bool {
                        $id = $record['id'] ?? null;

                        if (! is_scalar($id)) {
                            return false;
                        }

                        $idStr = (string) $id;
                        /** @var string $idStr */

                        return in_array($idStr, $selectedIds, true);
                    });

                    /** @var \Illuminate\Support\Collection<int, array<string, mixed>> $before */
                    /** @var \Illuminate\Support\Collection<int, array<string, mixed>> $after */
                    $records = $before->concat($after);
                }

                // Search against precomputed lowercase name for faster comparisons.
                if (filled($search)) {
                    $searchLower = Str::lower($search);

                    $records = $records->filter(function (array $record) use ($searchLower): bool {
                        $nameLower = $record['name_lower'] ?? null;

                        if (! is_scalar($nameLower)) {
                            return false;
                        }

                        $nameLowerStr = (string) $nameLower;
                        /** @var string $nameLowerStr */

                        return str_contains($nameLowerStr, $searchLower);
                    });
                }

                if (filled($sortColumn)) {
                    $records = $records->sortBy($sortColumn, SORT_REGULAR, $sortDirection === 'desc');
                }

                // Safely extract the 'set' filter value from the filters array.
                $filterSet = null;
                if (isset($filters['set']) && is_array($filters['set']) && array_key_exists('value', $filters['set'])) {
                    $filterSet = $filters['set']['value'];
                }

                if (filled($filterSet) && is_scalar($filterSet)) {
                    $records = $records->where('set', (string) $filterSet);
                }

                $keyedRecords = $records->mapWithKeys(function (array $record): array {
                    $id = $record['id'] ?? null;

                    if (is_scalar($id)) {
                        $key = (string) $id;
                    } else {
                        $key = '';
                    }

                    /** @var string $key */
                    return [$key => $record];
                });

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
                            ->tooltip(fn (array $record) => $record['name'] ?? '')
                            ->grow(false),
                        TextColumn::make('name')
                            ->size(TextSize::ExtraSmall)
                            ->limit(15)
                            ->wrap()
                            ->alignCenter()
                            ->visible(fn (Table $table) => $table->getArguments()['showIconLabels'] ?? false),
                    ])->alignment(Alignment::Center),
                ]),
            ])
            ->filters([
                SelectFilter::make('set')
                    ->options(
                        fn (Table $table) => collect($sets)
                            ->when(
                                function () use ($table) {
                                    $allowedSets = $table->getArguments()['sets'] ?? null;

                                    return is_array($allowedSets) && count($allowedSets) > 0;
                                },
                                function (Collection $sets) use ($table): Collection {
                                    $allowedSets = $table->getArguments()['sets'] ?? null;

                                    if (! is_array($allowedSets)) {
                                        return $sets;
                                    }

                                    $allowedSetsArr = array_values($allowedSets);
                                    /** @var array<int,string> $allowedSetsArr */

                                    return $sets->filter(fn ($_, $key) => in_array($key, $allowedSetsArr, true));
                                }
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
            ->contentGrid(
                fn (Table $table) => $table->getArguments()['showIconLabels'] ?? false
                    ? ['sm' => 2, 'md' => 3, 'lg' => 4, 'xl' => 6]
                    : ['sm' => 4, 'md' => 6, 'lg' => 8, 'xl' => 10]
            );
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

            // Defensive check: manifest may contain set names not present in $sets.
            if (! isset($sets[$setName])) {
                continue;
            }

            $prefixRaw = $sets[$setName]['prefix'] ?? $setName;
            if (! is_scalar($prefixRaw)) {
                $prefix = (string) $setName;
            } else {
                $prefix = (string) $prefixRaw;
            }

            foreach ($setGroups as $group) {
                if (! is_iterable($group)) {
                    continue;
                }

                foreach ($group as $icon) {
                    if (! is_scalar($icon)) {
                        continue;
                    }

                    /** @var string $iconName */
                    $iconName = (string) $icon;

                    if ($iconName === '') {
                        continue;
                    }

                    $records[] = [
                        'id' => $prefix . '-' . $iconName,
                        'name' => $iconName,
                        'name_lower' => Str::lower($iconName),
                        'set' => (string) $setName,
                    ];
                }
            }
        }

        return $records;
    }
}
