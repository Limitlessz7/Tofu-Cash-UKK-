<x-filament::page>
    <div class="space-y-6 pt-4">

        {{-- 🔹 Filter Rentang Waktu --}}
        <x-filament::section class="p-6 rounded-xl shadow-sm border border-gray-200 bg-white">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
                <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                    <label class="text-sm font-medium text-gray-600">Dari:</label>
                    <x-filament::input
                        type="date"
                        wire:model.live="startDate"
                        class="border-gray-300 rounded-lg px-3 py-2 text-sm"
                    />
                    <label class="text-sm font-medium text-gray-600">Sampai:</label>
                    <x-filament::input
                        type="date"
                        wire:model.live="endDate"
                        class="border-gray-300 rounded-lg px-3 py-2 text-sm"
                    />
                </div>

                <x-filament::button wire:click="$refresh" color="primary" icon="heroicon-o-magnifying-glass">
                    Tampilkan
                </x-filament::button>
            </div>
        </x-filament::section>

        {{-- 🔹 Total Pendapatan --}}
        <x-filament::section class="p-6 rounded-xl shadow-sm border border-gray-200 bg-white">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-semibold flex items-center gap-2">
                        <x-heroicon-o-banknotes class="w-5 h-5 text-green-600" />
                        Total Pendapatan
                    </h3>
                    <p class="text-4xl font-bold text-gray-800 mt-2">
                        Rp{{ number_format($this->getTotalPendapatan(), 0, ',', '.') }}
                    </p>
                    <p class="text-sm text-gray-500 mt-1">
                        Periode:
                        {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}
                        –
                        {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                    </p>
                </div>
            </div>
        </x-filament::section>

        {{-- 🔹 Grafik Pendapatan Harian --}}
        <x-filament::section class="p-6 rounded-xl shadow-sm border border-gray-200 bg-white">
            <h3 class="text-lg font-semibold flex items-center gap-2 mb-4">
                <x-heroicon-o-chart-bar class="w-5 h-5 text-blue-600" />
                Grafik Pendapatan   
            </h3>

            {{-- panggil widget grafik --}}
            @livewire(\App\Filament\Widgets\LaporanChart::class)
        </x-filament::section>

        {{-- 🔹 Daftar Transaksi --}}
        <x-filament::section class="p-6 rounded-xl shadow-sm border border-gray-200 bg-white">
            <h3 class="text-lg font-semibold flex items-center gap-2 mb-4">
                <x-heroicon-o-rectangle-stack class="w-5 h-5 text-indigo-600" />
                Daftar Transaksi
            </h3>
            {{ $this->table }}
        </x-filament::section>
    </div>
</x-filament::page>
