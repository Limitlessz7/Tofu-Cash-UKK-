<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Transaction;
use Carbon\Carbon;

class LaporanChart extends ChartWidget
{
    protected static ?string $heading = null;

    public ?string $start = null;
    public ?string $end = null;

    protected int|string|array $columnSpan = 12;

    protected function getData(): array
    {
        // Range default: 30 hari terakhir jika tidak ada filter
        $start = $this->start ? Carbon::parse($this->start) : now()->subDays(29);
        $end   = $this->end   ? Carbon::parse($this->end)   : now();

        // Buat range hari
        $range = collect();
        $d = $start->copy();
        while ($d <= $end) {
            $range->push($d->copy());
            $d->addDay();
        }

        // Label harian
        $labels = $range->map(fn($d) => $d->format('d M'));

        // Data pendapatan per hari
        $data = $range->map(fn($date) =>
            Transaction::where('status', 'paid')
                ->whereDate('transaction_date', $date)
                ->sum('total_price')
        );

        // Label dinamis berdasarkan range tanggal
        $dynamicLabel = "Pendapatan (" .
            $start->format('d M') . " – " . $end->format('d M') . ")";

        return [
            'datasets' => [
                [
                    'label' => $dynamicLabel,
                    'data' => $data,
                    'backgroundColor' => 'rgba(52, 211, 153, 0.7)',
                    'borderColor' => '#10b981',
                    'borderWidth' => 2,
                    'borderRadius' => 10,
                    'barPercentage' => 0.55,
                    'categoryPercentage' => 0.55,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,

            'animation' => [
                'duration' => 800,
                'easing' => 'easeOutQuart',
            ],

            'plugins' => [
                'legend' => [
                    'display' => true,
                    'labels' => [
                        'color' => '#d1d5db',
                    ],
                ],

                'tooltip' => [
                    'enabled' => true,
                    'displayColors' => false,
                    'backgroundColor' => 'rgba(31,41,55,0.9)',
                    'titleColor' => '#ffffff',
                    'bodyColor' => '#d1d5db',
                    'padding' => 10,
                    'titleFont' => [
                        'weight' => '600',
                        'size' => 14,
                    ],
                    'bodyFont' => [
                        'size' => 13,
                    ],

                    'callbacks' => [
                        'title' => fn($tooltipItems) => $tooltipItems[0]['label'],

                        'label' => 'function(context) {
                            let value = context.parsed.y ?? 0;
                            let formatted = new Intl.NumberFormat("id-ID").format(value);
                            return "Pendapatan: Rp " + formatted;
                        }',
                    ],
                ],
            ],

            'scales' => [
                'x' => [
                    'ticks' => [
                        'color' => '#9ca3af',
                    ],
                    'grid' => [
                        'display' => false,
                    ],
                ],
                'y' => [
                    'ticks' => [
                        'color' => '#9ca3af',
                        'callback' => 'value => "Rp " + new Intl.NumberFormat("id-ID").format(value)',
                    ],
                    'grid' => [
                        'color' => 'rgba(255,255,255,0.06)',
                        'drawBorder' => false,
                    ],
                ],
            ],

            'elements' => [
                'bar' => [
                    'borderRadius' => 10,
                    'hoverBackgroundColor' => 'rgba(34,197,94,0.6)',
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    // 🔄 Auto-refresh ketika filter tanggal berubah
    protected function getListeners(): array
    {
        return [
            'start' => '$refresh',
            'end' => '$refresh',
        ];
    }
}
