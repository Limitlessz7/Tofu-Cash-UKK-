<x-filament::page>
    <div class="space-y-6 pt-2">

        {{-- FILTER RANGE TANGGAL --}}
        <div class="p-4 border rounded-xl bg-white shadow-sm flex flex-wrap items-end gap-5">
            <div class="flex flex-col">
                <label class="text-xs font-semibold text-gray-600 mb-1">Dari</label>
                <input type="date"
                       wire:model.live="startDate"
                       class="border-gray-300 rounded-lg px-3 py-1.5 text-sm w-40 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="flex flex-col">
                <label class="text-xs font-semibold text-gray-600 mb-1">Sampai</label>
                <input type="date"
                       wire:model.live="endDate"
                       class="border-gray-300 rounded-lg px-3 py-1.5 text-sm w-40 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <button wire:click="$refresh"
                class="ml-auto px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow flex items-center gap-2"
                style="background-color: #e39f00ff;">
                <x-heroicon-o-funnel class="w-4 h-4"/>
                Terapkan
            </button>
        </div>


        {{-- TOTAL PENDAPATAN (tetap tampil) --}}
        <div class="p-6 rounded-xl border bg-white shadow-sm">
            <h3 class="text-lg font-semibold mb-1 flex items-center gap-2">
                <x-heroicon-o-banknotes class="w-5 h-5 text-dark-600"/>
                Total Pendapatan
            </h3>

            <p class="text-3xl font-bold text-gray-800">
                Rp{{ $startDate && $endDate ? number_format($this->totalPendapatan, 0, ',', '.') : '0' }}
            </p>

            <p class="text-sm text-gray-500 mt-1">
                Periode:
                <strong>
                    {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d/m/Y') : '-' }}
                    –
                    {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d/m/Y') : '-' }}
                </strong>
            </p>
        </div>


        {{-- GRAFIK (tetap tampil, tapi kosong sebelum filter) --}}
        <div class="p-6 rounded-xl border bg-white shadow-sm">
            <h3 class="text-xl font-semibold mb-3 flex items-center gap-2">
                <x-heroicon-o-chart-bar class="w-6 h-6 text-gray-700"/>
                Grafik Pendapatan
            </h3>

            @livewire(\App\Filament\Widgets\LaporanChart::class, [
                'start' => $startDate,
                'end'   => $endDate
            ])
        </div>


        {{-- TABEL TRANSAKSI (tetap tampil, tapi kosong sebelum filter) --}}
        <div class="p-6 rounded-xl border bg-white shadow-sm">
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <x-heroicon-o-rectangle-stack class="w-5 h-5 text-indigo-600"/>
                Daftar Transaksi
            </h3>

            {{ $this->table }}
        </div>

    </div>
</x-filament::page>
