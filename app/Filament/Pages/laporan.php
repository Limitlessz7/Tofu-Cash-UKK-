<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget\Stat;

class Laporan extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Laporan Pendapatan';
    protected static ?string $title = 'Laporan Pendapatan';
    protected static string $view = 'filament.pages.laporan';

    public string $range = 'today'; // default

    public function mount(): void
    {
        $this->range = 'today';
    }

    /**
     * Tentukan range tanggal 
     */
    protected function getDateRange(): array
    {
        return match ($this->range) {
            'week'  => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            default => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
        };
    }

    /**
     * Hitung total pendapatan di range waktu tertentu
     */
    protected function getTotalPendapatan(): float
    {
        [$start, $end] = $this->getDateRange();

        return (float) Transaction::whereBetween('transaction_date', [$start, $end])
            ->where('status', 'paid') // ✅ hanya transaksi yang sudah dibayar
            ->sum('total_price');
    }

    /**
     * Tampilkan data transaksi dalam tabel
     */
    public function table(Table $table): Table
    {
        [$start, $end] = $this->getDateRange();

        return $table
            ->query(
                Transaction::query()
                    ->whereBetween('transaction_date', [$start, $end])
                    ->where('status', 'paid') // ✅ hanya transaksi paid
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Tanggal')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total (Rp)')
                    ->money('IDR', true)
                    ->sortable(),
            ])
            ->paginated()
            ->defaultSort('transaction_date', 'desc')
            ->emptyStateHeading('Tidak ada transaksi untuk periode ini.');
    }

    /**
     * Heading dinamis berdasarkan range
     */
    public function getHeading(): string
    {
        return 'Laporan Pendapatan (' . $this->getRangeLabel() . ')';
    }

    private function getRangeLabel(): string
    {
        return match ($this->range) {
            'week'  => 'Minggu Ini',
            'month' => 'Bulan Ini',
            default => 'Hari Ini',
        };
    }

    public function updatedRange(): void
    {
        $this->dispatch('$refresh');
    }

    /**
     * Widget ringkasan di atas tabel
     */
    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\LaporanOverview::class,
        ];
    }

    /**
     * Export PDF
     */
    public function exportPdf()
    {
        $data = [
            'title' => 'Laporan Pendapatan ' . $this->getRangeLabel(),
            'rangeLabel' => $this->getRangeLabel(),
            'total' => $this->getTotalPendapatan(),
            'transactions' => Transaction::whereBetween('transaction_date', $this->getDateRange())
                ->where('status', 'paid') // ✅ hanya paid
                ->get(),
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.laporan', $data)
            ->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'laporan-' . now()->format('Y-m-d') . '.pdf'
        );
    }
}
