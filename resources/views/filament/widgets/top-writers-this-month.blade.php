<x-filament-widgets::widget class="dashboard-ranking-widget">
    <x-filament::section>
        <div class="dashboard-ranking-header">
            <div class="dashboard-ranking-title flex items-start gap-3">
                <div class="dashboard-ranking-icon dashboard-ranking-icon--amber">
                    <x-filament::icon icon="heroicon-o-trophy" class="h-5 w-5" />
                </div>
                <div class="min-w-0">
                    <h2 class="text-base font-semibold text-gray-950 dark:text-white">Penulis teratas</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Performa {{ $monthOptions[$selectedMonth] }} {{ $selectedYear }}</p>
                </div>
            </div>
            <div class="dashboard-ranking-filters" aria-label="Filter penulis teratas">
                <x-filament::input.wrapper inline-prefix>
                    <x-filament::input.select wire:model.live="selectedMonth" aria-label="Pilih bulan">
                        @foreach ($monthOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
                <x-filament::input.wrapper inline-prefix>
                    <x-filament::input.select wire:model.live="selectedYear" aria-label="Pilih tahun">
                        @foreach ($yearOptions as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>
        </div>

        @if ($users->isEmpty())
            <x-filament::empty-state
                heading="Belum ada laporan"
                description="Belum ada data penulis untuk periode ini."
                icon="heroicon-o-document-text"
                class="mt-6"
            />
        @else
            <div class="dashboard-ranking-table-wrap mt-5 overflow-x-auto">
                <table class="dashboard-ranking-table" aria-label="Penulis teratas sesuai filter bulan dan tahun">
                    <thead>
                        <tr>
                            <th class="dashboard-ranking-table__rank" scope="col">Peringkat</th>
                            <th class="dashboard-ranking-table__name" scope="col">Nama</th>
                            <th class="dashboard-ranking-table__count" scope="col">Laporan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td class="dashboard-ranking-table__rank">
                                    <span class="dashboard-rank-badge {{ $loop->first ? 'is-first' : '' }}">{{ $loop->iteration }}</span>
                                </td>
                                <td class="dashboard-ranking-table__name">
                                    <div class="dashboard-ranking-person flex items-center gap-3">
                                        <span class="dashboard-avatar">{{ str($user->name)->substr(0, 1)->upper() }}</span>
                                        <span class="dashboard-ranking-name font-medium text-gray-950 dark:text-white">{{ $user->name }}</span>
                                    </div>
                                </td>
                                <td class="dashboard-ranking-table__count">
                                    <span class="dashboard-report-count">{{ number_format($user->reports_count) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
