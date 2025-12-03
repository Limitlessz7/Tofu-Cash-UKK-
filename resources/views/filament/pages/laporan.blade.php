<x-filament::page>
    <div class="space-y-6 pt-2">

        {{-- FILTER RANGE TANGGAL --}}
        <div class="p-4 border rounded-xl bg-white dark:bg-gray-800 dark:border-gray-700 shadow-sm flex flex-wrap items-end gap-5">
            <div class="flex flex-col">
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Dari</label>
                <input type="date"
                       wire:model.live="startDate"
                       class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-1.5 text-sm w-40 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="flex flex-col">
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Sampai</label>
                <input type="date"
                       wire:model.live="endDate"
                       class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-lg px-3 py-1.5 text-sm w-40 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <button wire:click="$refresh"
                class="ml-auto px-4 py-2 rounded-lg bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-semibold shadow flex items-center gap-2">
                <x-heroicon-o-funnel class="w-4 h-4"/>
                Terapkan
            </button>
        </div>


        {{-- TOTAL PENDAPATAN --}}
        <div class="p-6 rounded-xl border bg-white dark:bg-gray-800 dark:border-gray-700 shadow-sm">
            <h3 class="text-lg font-semibold mb-1 flex items-center gap-2 text-gray-800 dark:text-gray-100">
                <x-heroicon-o-banknotes class="w-5 h-5 text-gray-600 dark:text-gray-300"/>
                Total Pendapatan
            </h3>

            <p class="text-3xl font-bold text-gray-800 dark:text-gray-100">
                Rp{{ $startDate && $endDate ? number_format($this->totalPendapatan, 0, ',', '.') : '0' }}
            </p>

            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Periode:
                <strong class="text-gray-700 dark:text-gray-300">
                    {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d/m/Y') : '-' }}
                    –
                    {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d/m/Y') : '-' }}
                </strong>
            </p>
        </div>


        {{-- GRAFIK --}}
        <div class="p-6 rounded-xl border bg-white dark:bg-gray-800 dark:border-gray-700 shadow-sm">
            <h3 class="text-xl font-semibold mb-3 flex items-center gap-2 text-gray-800 dark:text-gray-100">
                <x-heroicon-o-chart-bar class="w-6 h-6 text-gray-700 dark:text-gray-300"/>
                Grafik Pendapatan
            </h3>

            @livewire(\App\Filament\Widgets\LaporanChart::class, [
                'start' => $startDate,
                'end'   => $endDate
            ])
        </div>


        {{-- TABEL TRANSAKSI --}}
        <div class="p-6 rounded-xl border bg-white dark:bg-gray-800 dark:border-gray-700 shadow-sm">
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2 text-gray-800 dark:text-gray-100">
                <x-heroicon-o-rectangle-stack class="w-5 h-5 text-indigo-600 dark:text-indigo-400"/>
                Daftar Transaksi
            </h3>

            {{ $this->table }}
        </div>

    </div>
</x-filament::page>
