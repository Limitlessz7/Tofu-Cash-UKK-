<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Transaction;
use Carbon\Carbon;

class LaporanOverview extends BaseWidget
{
    public ?string $range = 'today';

    protected function getStats(): array
    {
        [$start, $end] = $this->getDateRange();

        // ✅ Hanya ambil transaksi dengan status "paid"
        $total = (float) Transaction::where('status', 'paid')
            ->whereBetween('transaction_date', [$start, $end])
            ->sum('total_price');

        return [
            Stat::make('Total Pendapatan', 'Rp ' . number_format($total, 0, ',', '.'))
                ->description($this->getRangeLabel())
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('success'),
        ];
    }

    protected function getDateRange(): array
    {
        return match ($this->range) {
            'week'  => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            default => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
        };
    }

    private function getRangeLabel(): string
    {
        return match ($this->range) {
            'week'  => 'Minggu Ini',
            'month' => 'Bulan Ini',
            default => 'Hari Ini',
        };
    }
}
