<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Transaction;
use Carbon\Carbon;

class LaporanChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Pendapatan';

    public ?string $range = 'today';

    protected function getData(): array
    {
        [$start, $end] = $this->getDateRange();

        if ($this->range === 'month') {
            $period = collect(range(1, Carbon::now()->daysInMonth))
                ->map(fn ($day) => Carbon::now()->startOfMonth()->addDays($day - 1));
        } elseif ($this->range === 'week') {
            $period = collect(range(0, 6))
                ->map(fn ($i) => Carbon::now()->startOfWeek()->addDays($i));
        } else {
            $period = collect([Carbon::today()]);
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
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['labels' => ['color' => '#d1d5db']],
            ],
            'scales' => [
                'x' => ['ticks' => ['color' => '#9ca3af']],
                'y' => [
                    'ticks' => [
                        'color' => '#9ca3af',
                        'callback' => 'value => "Rp " + new Intl.NumberFormat("id-ID").format(value)',
                    ],
                    'grid' => ['color' => 'rgba(255,255,255,0.05)'],
                ],
            ],
            'elements' => [
                'bar' => [
                    'borderRadius' => 6,
                    'shadowOffsetX' => 2,
                    'shadowOffsetY' => 3,
                    'shadowBlur' => 8,
                    'shadowColor' => 'rgba(0,0,0,0.25)',
                ],
            ],
        ];
    }

    protected function getDateRange(): array
    {
        return match ($this->range) {
            'week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            default => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
        };
    }
}
