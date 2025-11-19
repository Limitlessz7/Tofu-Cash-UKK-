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

    public ?string $startDate = null;
    public ?string $endDate = null;

    /**
     * RANGE TANGGAL
     */
    protected function getDateRange(): array
    {
        if ($this->startDate && $this->endDate) {
            return [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay()
            ];
        }

        // Default 30 hari terakhir
        $end = Carbon::now()->endOfDay();
        $start = $end->copy()->subDays(29)->startOfDay();
        return [$start, $end];
    }

    /**
     * COMPUTED PROPERTY
     */
    public function getTotalPendapatanProperty(): float
    {
        [$start, $end] = $this->getDateRange();

        return (float) Transaction::where('status', 'paid')
            ->whereBetween('transaction_date', [$start, $end])
            ->sum('total_price');
    }

    // Wrapper agar pemanggilan lama tetap aman
    public function getTotalPendapatan(): float
    {
        return $this->totalPendapatan;
    }

    /**
     * TABEL TRANSAKSI
     */
    public function table(Table $table): Table
    {
        [$start, $end] = $this->getDateRange();

        $query = Transaction::query()
            ->with('items.product')
            ->where('status', 'paid')
            ->whereBetween('transaction_date', [$start, $end]);

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Tanggal')
                    ->dateTime('d/m/Y H:i'),

                Tables\Columns\TextColumn::make('items_summary')
                    ->label('Produk & Jumlah')
                    ->getStateUsing(fn ($record) =>
                        $record->items
                            ->map(fn ($i) => ($i->product->name ?? '-') . ' ×' . $i->quantity)
                            ->join(', ')
                    ),

                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total(Rp)')
                    ->money('IDR', true),
            ])
            ->defaultSort('transaction_date', 'desc')
            ->paginated(10);
    }

    /**
     * Refresh otomatis saat tanggal berubah
     */
    public function updatedStartDate() { $this->dispatch('$refresh'); }
    public function updatedEndDate()   { $this->dispatch('$refresh'); }

    /**
     * EXPORT PDF
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('exportPdf')
                ->label('Export PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(fn () => $this->exportPdf()),
        ];
    }

    public function exportPdf()
    {
        [$start, $end] = $this->getDateRange();

        $transactions = Transaction::with('items.product')
            ->where('status', 'paid')
            ->whereBetween('transaction_date', [$start, $end])
            ->get();

        $rangeLabel = $start->format('d M') . ' – ' . $end->format('d M');

        $data = [
            'title' => 'Laporan Pendapatan',
            'range' => [
                'start' => $start->format('d/m/Y'),
                'end'   => $end->format('d/m/Y'),
            ],
            'formattedTotal' => 'Rp ' . number_format($this->totalPendapatan, 0, ',', '.'),
            'transactions' => $transactions,
            'rangeLabel' => $rangeLabel,
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.laporan', $data);

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'laporan-' . now()->format('Y-m-d') . '.pdf'
        );
    }
}
