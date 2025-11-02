<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\Carbon;

class DashboardCustom extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.pages.dashboard-custom';
    protected static ?string $title = 'Dashboard';

    public int $todayCount = 0;
    public int $monthSold = 0;

    public function mount(): void
    {
        // Total transaksi hari ini
        $this->todayCount = Transaction::where('status', 'paid')
            ->whereDate('transaction_date', Carbon::today())
            ->count();

        // Total produk terjual bulan ini
        $this->monthSold = TransactionItem::whereHas('transaction', function ($q) {
                $q->where('status', 'paid')
                  ->whereMonth('transaction_date', now()->month)
                  ->whereYear('transaction_date', now()->year);
            })
            ->sum('quantity');
    }
}
    