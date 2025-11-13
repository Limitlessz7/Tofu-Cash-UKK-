<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Transaction;
use Carbon\Carbon;

class LaporanChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Pendapatan';

    public ?string $startDate = null;
    public ?string $endDate = null;

    // 🟩 inject dari Laporan page
    protected static ?string $pollingInterval = null;

    protected function getData(): array
    {
        $start = $this->startDate ? Carbon::parse($this->startDate) : Carbon::now()->startOfMonth();
        $end = $this->endDate ? Carbon::parse($this->endDate) : Carbon::now()->endOfMonth();

        // generate setiap hari dalam range
        $period = collect();
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $period->push($date->copy());
        }

        $labels = $period->map(fn ($date) => $date->translatedFormat('d M'));
        $data = $period->map(fn ($date) =>
            Transaction::where('status', 'paid')
                ->whereDate('transaction_date', $date)
                ->sum('total_price')
        );

        return [
            'datasets' => [[
                'label' => 'Pendapatan',
                'data' => $data,
                'borderColor' => '#10b981',
                'backgroundColor' => 'rgba(16,185,129,0.2)',
                'tension' => 0.4,
                'borderWidth' => 3,
                'fill' => true,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    // optional: tampilan chart lebih halus
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['labels' => ['color' => '#374151']],
            ],
            'scales' => [
                'x' => ['ticks' => ['color' => '#6b7280']],
                'y' => [
                    'ticks' => [
                        'color' => '#6b7280',
                        'callback' => 'value => "Rp " + new Intl.NumberFormat("id-ID").format(value)',
                    ],
                    'grid' => ['color' => 'rgba(0,0,0,0.05)'],
                ],
            ],
        ];
    }
}
