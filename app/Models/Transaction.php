<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'transactions';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected ?string $resolvedPrimaryKey = null;

    protected $fillable = [
        'transaction_date',
        'total_price',
        'paid_amount',
        'change_amount',
        'status',
        'created_by',
    ];

    // ✅ Fix cast biar format tanggal aman di Filament & PDF
    protected $casts = [
        'transaction_date' => 'datetime',
    ];

    /**
     * 🔹 Relasi ke TransactionItem
     */
    public function items(): HasMany
    {
        $fk = $this->resolveTransactionItemsForeignKey();
        return $this->hasMany(TransactionItem::class, $fk, $this->getKeyName());
    }

    /**
     * 🔹 Deteksi kolom foreign key di transaction_items
     */
    protected function resolveTransactionItemsForeignKey(): string
    {
        $table = 'transaction_items';
        $candidates = [
            'trxi_transaction_id',
            'trx_transaction_id',
            'transaction_id',
            'transactions_id',
        ];

        foreach ($candidates as $col) {
            if (Schema::hasColumn($table, $col)) {
                return $col;
            }
        }

        return 'transaction_id'; // fallback
    }

    /**
     * 🔹 Pilih primary key dinamis (trx_id / id)
     */
    public function getKeyName()
    {
        if ($this->resolvedPrimaryKey !== null) {
            return $this->resolvedPrimaryKey;
        }

        $table = $this->getTable();

        if (Schema::hasColumn($table, 'trx_id')) {
            $key = 'trx_id';
        } elseif (Schema::hasColumn($table, 'id')) {
            $key = 'id';
        } else {
            $key = parent::getKeyName();
        }

        $this->resolvedPrimaryKey = $key;
        return $this->resolvedPrimaryKey;
    }

    /**
     * 🔹 Virtual Attribute — hitung total dari items
     */
    public function getTotalAttribute(): float
    {
        $itemsTable = 'transaction_items';

        $subtotalColumn = Schema::hasColumn($itemsTable, 'trxi_subtotal')
            ? 'trxi_subtotal'
            : (Schema::hasColumn($itemsTable, 'subtotal') ? 'subtotal' : 'trxi_subtotal');

        return (float) $this->items()->sum($subtotalColumn);
    }

    /**
     * 🔹 Saat transaksi disimpan, update total otomatis
     */
    protected static function booted()
    {
        static::saving(function ($transaction) {
            $itemsTable = 'transaction_items';
            $subtotalColumn = Schema::hasColumn($itemsTable, 'trxi_subtotal')
                ? 'trxi_subtotal'
                : (Schema::hasColumn($itemsTable, 'subtotal') ? 'subtotal' : null);

            if ($subtotalColumn) {
                $transaction->total_price = $transaction->items()->sum($subtotalColumn);
            } else {
                $transaction->total_price = $transaction->items->sum(function ($item) {
                    $qtyColumn = Schema::hasColumn('transaction_items', 'trxi_qty') ? 'trxi_qty' : 'quantity';
                    $priceColumn = Schema::hasColumn('transaction_items', 'trxi_price') ? 'trxi_price' : 'price';
                    return $item->{$qtyColumn} * $item->{$priceColumn};
                });
            }
        });
    }

    /**
     * 🔹 Scope untuk transaksi yang sudah dibayar
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }
}
