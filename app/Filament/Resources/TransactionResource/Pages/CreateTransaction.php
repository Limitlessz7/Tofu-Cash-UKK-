<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    /**
     * Mutasi data form sebelum create (gunakan logika dari Resource)
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return TransactionResource::mutateFormDataBeforeCreate($data);
    }

    /**
     * Setelah transaksi dibuat, hitung ulang total dan simpan change_amount
     */
    protected function afterCreate(): void
    {
        $total = $this->record->items()->sum('subtotal');
        $paid = $this->record->paid_amount ?? 0;

        $this->record->update([
            'total' => $total,
            'change_amount' => max(0, $paid - $total),
        ]);
    }
}
