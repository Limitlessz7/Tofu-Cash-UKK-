<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Transaction;
use Carbon\Carbon;

class LaporanOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $chartData = collect(range(6, 0))->map(function ($i) {
            $date = Carbon::today()->subDays($i);
            return Transaction::where('status', 'paid')
                ->whereDate('transaction_date', $date)
                ->sum('total_price');
        });

        $totalHariIni   = $chartData->last();
        $totalKemarin   = $chartData->slice(-2, 1)->first() ?? 0;
        $persentaseNaik = $totalKemarin > 0
            ? (($totalHariIni - $totalKemarin) / $totalKemarin) * 100
            : 0;

        $chartValues = $chartData->toArray();

        $deskripsi = $persentaseNaik > 0
            ? '+' . number_format($persentaseNaik, 1) . '% dibanding kemarin'
            : ($persentaseNaik < 0
                ? number_format($persentaseNaik, 1) . '% turun dari kemarin'
                : 'Stabil dibanding kemarin');

        return [
            Stat::make('Total Pendapatan Hari Ini', 'Rp ' . number_format($totalHariIni, 0, ',', '.'))
                ->description($deskripsi)
                ->descriptionIcon('heroicon-o-currency-dollar') // 💰 Ikon uang
                ->color('success') // Selalu hijau (cuan)
                ->chart($chartValues)
                ->extraAttributes([
                    'class' => '
                        bg-gradient-to-br from-emerald-400/20 to-green-500/10
                        dark:from-emerald-500/10 dark:to-green-400/5
                        border border-emerald-400/30 dark:border-emerald-500/30
                        rounded-2xl shadow-[0_4px_25px_rgba(0,0,0,0.25)]
                        transition hover:scale-[1.01]
                    ',
                ]),
        ];
    }
}
