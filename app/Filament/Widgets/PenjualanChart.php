<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Transaction;
use Carbon\Carbon;

class PenjualanChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Penjualan';

    protected function getData(): array
    {
        // Ambil data 7 hari terakhir
        $dates = collect(range(6, 0))->map(fn ($i) => Carbon::today()->subDays($i));
        $labels = $dates->map(fn ($date) => $date->translatedFormat('d M'));

        // ✅ Hanya transaksi dengan status "paid"
        $data = $dates->map(function ($date) {
            return Transaction::where('status', 'paid')
                ->whereDate('transaction_date', $date)
                ->sum('total_price');
        });

        return [
            'datasets' => [
                [
                    'label' => 'Total Penjualan',
                    'data' => $data,
                    'fill' => 'start',
                    'tension' => 0.4,
                    'borderWidth' => 3,
                    'borderColor' => '#fbbf24',
                    'backgroundColor' => 'rgba(251, 191, 36, 0.2)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line'; // line chart
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => true],
            ],
            'scales' => [
                'y' => [
                    'ticks' => [
                        'callback' => 'value => "Rp " + new Intl.NumberFormat("id-ID").format(value)',
                    ],
                ],
            ],
        ];
    }
}
