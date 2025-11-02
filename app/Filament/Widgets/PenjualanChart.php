<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Transaction;
use Carbon\Carbon;

class PenjualanChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Penjualan 7 Hari Terakhir';

    protected function getData(): array
    {
        $dates = collect(range(6, 0))->map(fn ($i) => Carbon::today()->subDays($i));
        $labels = $dates->map(fn ($date) => $date->translatedFormat('d M'));

        $data = $dates->map(fn ($date) =>
            Transaction::where('status', 'paid')
                ->whereDate('transaction_date', $date)
                ->sum('total_price')
        );

        return [
            'datasets' => [
                [
                    'label' => 'Pendapatan',
                    'data' => $data,
                    'borderWidth' => 3,
                    'tension' => 0.4,
                    'borderColor' => '#facc15', // kuning lembut
                    'backgroundColor' => 'rgba(250, 204, 21, 0.15)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    // 💡 tambahan efek shadow abu dan font warna responsif
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'labels' => ['color' => '#d1d5db'],
                ],
            ],
            'scales' => [
                'x' => [
                    'grid' => ['color' => 'rgba(255,255,255,0.05)'],
                    'ticks' => ['color' => '#9ca3af'],
                ],
                'y' => [
                    'grid' => ['color' => 'rgba(255,255,255,0.05)'],
                    'ticks' => [
                        'color' => '#9ca3af',
                        'callback' => 'value => "Rp " + new Intl.NumberFormat("id-ID").format(value)',
                    ],
                ],
            ],
            'elements' => [
                'line' => [
                    'borderJoinStyle' => 'round',
                    'shadowOffsetX' => 2,
                    'shadowOffsetY' => 3,
                    'shadowBlur' => 8,
                    'shadowColor' => 'rgba(0,0,0,0.25)',
                ],
                'point' => [
                    'radius' => 3,
                    'hoverRadius' => 6,
                    'backgroundColor' => '#fde047',
                    'borderColor' => '#facc15',
                ],
            ],
        ];
    }
}
