@php
use Carbon\Carbon;
use App\Models\Transaction;
use App\Models\TransactionItem;

$todayCount = Transaction::where('status', 'paid')
    ->whereDate('transaction_date', Carbon::today())
    ->count();

$monthSold = TransactionItem::whereHas('transaction', function ($q) {
        $q->where('status', 'paid')
          ->whereMonth('transaction_date', now()->month)
          ->whereYear('transaction_date', now()->year);
    })
    ->sum('quantity');
@endphp

<x-filament::page>
    <div class="p-8 space-y-8 bg-gray-950 text-gray-100 min-h-screen">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <h2 class="text-3xl font-bold text-amber-400 drop-shadow-[0_0_4px_rgba(251,191,36,0.4)]">
                Dashboard
            </h2>
            <span class="text-sm text-gray-400">
                {{ now()->translatedFormat('l, d F Y') }}

            </span>
        </div>

        {{-- Grid utama --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Total Pendapatan --}}
            <div class="p-6 bg-gray-900/90 backdrop-blur-md rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.5)] hover:shadow-[0_4px_25px_rgba(251,191,36,0.15)] transition">
                @livewire(\App\Filament\Widgets\LaporanOverview::class)
            </div>

            {{-- Grafik Penjualan --}}
            <div class="lg:col-span-2 p-6 bg-gray-900/90 backdrop-blur-md rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.5)] hover:shadow-[0_4px_25px_rgba(251,191,36,0.15)] transition">
                @livewire(\App\Filament\Widgets\PenjualanChart::class)
            </div>
        </div>
    </div>
</x-filament::page>
