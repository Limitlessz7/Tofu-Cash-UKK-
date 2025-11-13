<?php

namespace App\Filament\Pages;

use App\Models\Transaction;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;

class Laporan extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Laporan Pendapatan';
    protected static ?string $title = 'Laporan Pendapatan';
    protected static string $view = 'filament.pages.laporan';

    public ?string $startDate = null;
    public ?string $endDate = null;

    public function mount(): void
    {
        $this->startDate = Carbon::today()->startOfDay()->toDateString();
        $this->endDate = Carbon::today()->endOfDay()->toDateString();
    }

    protected function getTotalPendapatan(): float
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        return (float) Transaction::whereBetween('transaction_date', [$start, $end])
            ->where('status', 'paid')
            ->sum('total_price');
    }

    public function table(Table $table): Table
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

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
                    ->getStateUsing(fn ($record) =>
                        $record->items
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

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('exportPdf')
                ->label('Export PDF')
                ->button()
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(fn () => $this->exportPdf()),
        ];
    }

    public function exportPdf()
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();
        $total = $this->getTotalPendapatan();

        $transactions = Transaction::with('items.product')
            ->whereBetween('transaction_date', [$start, $end])
            ->where('status', 'paid')
            ->get();

        $data = [
            'title' => 'Laporan Pendapatan',
            'rangeLabel' => $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y'),
            'formattedTotal' => 'Rp ' . number_format($total, 0, ',', '.'),
            'transactions' => $transactions,
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.laporan', $data)
            ->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'laporan-' . now()->format('Y-m-d') . '.pdf'
        );
    }

    public function updated($property): void
    {
        if (in_array($property, ['startDate', 'endDate'])) {
            $this->dispatch('$refresh');
        }
    }
}
