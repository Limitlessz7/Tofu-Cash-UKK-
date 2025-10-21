<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Resources\Pages\EditRecord;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    /**
     * Mutasi data form sebelum update (gunakan logika dari Resource)
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return TransactionResource::mutateFormDataBeforeSave($data);
    }

    /**
     * Setelah transaksi diubah, hitung ulang total dan change_amount
     */
    protected function afterSave(): void
    {
        $total = $this->record->items()->sum('subtotal');
        $paid = $this->record->paid_amount ?? 0;

        $this->record->update([
            'total' => $total,
            'change_amount' => max(0, $paid - $total),
        ]);
    }
}
