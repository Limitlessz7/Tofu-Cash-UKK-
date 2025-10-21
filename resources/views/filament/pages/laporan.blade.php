<x-filament::page>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold">
                Laporan Pendapatan ({{ $this->getRangeLabel() }})
            </h2>

            <select wire:model.live="range" class="border rounded-lg px-3 py-2 text-sm">
                <option value="today">Hari Ini</option>
                <option value="week">Minggu Ini</option>
                <option value="month">Bulan Ini</option>
            </select>
        </div>

        <div class="text-lg font-medium">
            Total Pendapatan:
            <span class="font-bold text-green-600">
                Rp{{ number_format($this->getTotalPendapatan(), 0, ',', '.') }}
            </span>
        </div>

        {{ $this->table }}

        <div class="pt-4 flex justify-center">
            <x-filament::button
                wire:click="exportPdf"
                color="warning"
                icon="heroicon-o-printer"
            >
                Cetak PDF
            </x-filament::button>
        </div>
    </div>
</x-filament::page>
