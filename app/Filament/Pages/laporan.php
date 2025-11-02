<?php

namespace App\Filament\Pages;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;

class Laporan extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Laporan Pendapatan';
    protected static ?string $title = 'Laporan Pendapatan';
    protected static string $view = 'filament.pages.laporan';

    public string $range = 'today';

    /** =====================
     *  🔹 Helper Range Tanggal
     *  ===================== */
    protected function getDateRange(): array
    {
        return match ($this->range) {
            'week'  => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            default => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
        };
    }

    /** =====================
     *  🔹 Total Pendapatan
     *  ===================== */
    protected function getTotalPendapatan(): float
    {
        [$start, $end] = $this->getDateRange();

        return (float) Transaction::whereBetween('transaction_date', [$start, $end])
            ->where('status', 'paid')
            ->sum('total_price');
    }

    /** =====================
     *  🔹 Tabel Transaksi
     *  ===================== */
    public function table(Table $table): Table
    {
        [$start, $end] = $this->getDateRange();

        return $table
            ->query(
                Transaction::query()
                    ->with('items.product')
                    ->whereBetween('transaction_date', [$start, $end])
                    ->where('status', 'paid')
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

                Tables\Columns\TextColumn::make('items_summary')
                    ->label('Produk & Jumlah')
                    ->getStateUsing(fn ($record) => $record->items
                        ->map(fn ($i) => ($i->product->name ?? '-') . ' ×' . $i->quantity)
                        ->join(', ')
                    )
                    ->limit(60),

                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total (Rp)')
                    ->money('IDR', true)
                    ->sortable(),
            ])
            ->defaultSort('transaction_date', 'desc')
            ->paginated(10)
            ->emptyStateHeading('Tidak ada transaksi untuk periode ini.');
    }

    /** =====================
     *  🔹 Label Heading
     *  ===================== */
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

    /** =====================
     *  🔹 Tombol Header
     *  ===================== */
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('exportPdf')
                ->label('Export PDF')
                ->button()
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(fn() => $this->exportPdf()),
        ];
    }

    /** =====================
     *  🔹 Export ke PDF
     *  ===================== */
    public function exportPdf()
    {
        [$start, $end] = $this->getDateRange();
        $total = $this->getTotalPendapatan();

        $transactions = Transaction::with('items.product')
            ->whereBetween('transaction_date', [$start, $end])
            ->where('status', 'paid')
            ->get();

        $data = [
            'title' => 'Laporan Pendapatan ' . $this->getRangeLabel(),
            'rangeLabel' => $this->getRangeLabel(),
            'formattedTotal' => 'Rp ' . number_format($total, 0, ',', '.'),
            'transactions' => $transactions,
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.laporan', $data)
            ->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'laporan-' . now()->format('Y-m-d') . '.pdf'
        );
    }
}
