<x-filament::page>
    <div class="space-y-6 pt-4">

        {{-- Filter Rentang Waktu + Tombol Export --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center space-x-2">
                <label class="text-sm font-medium text-gray-600">Periode:</label>
                <select wire:model.live="range" class="border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-primary-500 focus:border-primary-500">
                    <option value="today">Hari Ini</option>
                    <option value="week">Minggu Ini</option>
                    <option value="month">Bulan Ini</option>
                </select>
            </div>
        </div>

        {{-- Card Total Pendapatan --}}
        <x-filament::section class="p-6 rounded-xl shadow-sm border border-gray-200 bg-white">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-semibold flex items-center gap-2">
                        <x-heroicon-o-banknotes class="w-5 h-5 text-green-600" />
                        Total Pendapatan {{ $this->getRangeLabel() }}
                    </h3>
                    <p class="text-4xl font-bold text-gray-800 mt-2">
                        Rp{{ number_format($this->getTotalPendapatan(), 0, ',', '.') }}
                    </p>
                    <p class="text-sm text-green-600 mt-1 flex items-center">
                        <x-heroicon-o-chart-bar class="w-4 h-4 mr-1" />
                        Stabil dibanding kemarin
                    </p>
                </div>
            </div>
        </x-filament::section>


        {{-- Daftar Transaksi --}}
        <x-filament::section class="p-6 rounded-xl shadow-sm border border-gray-200 bg-white">
            <h3 class="text-lg font-semibold flex items-center gap-2 mb-4">
                <x-heroicon-o-rectangle-stack class="w-5 h-5 text-indigo-600" />
                Daftar Transaksi
            </h3>

            {{ $this->table }}
        </x-filament::section>
    </div>
</x-filament::page>
