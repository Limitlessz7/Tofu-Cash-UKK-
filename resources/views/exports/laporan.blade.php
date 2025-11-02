<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        h1 { text-align: center; font-size: 18px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; vertical-align: top; }
        th { background: #f2f2f2; }
        tfoot td { font-weight: bold; background: #fafafa; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p><strong>Periode:</strong> {{ $rangeLabel }}</p>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tanggal</th>
                <th>Produk & Jumlah</th>
                <th>Total (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $trx)
                <tr>
                    <td>{{ $trx->id }}</td>
                    <td>{{ $trx->transaction_date?->format('d-m-Y H:i') ?? '-' }}</td>
                    <td>
                        @foreach ($trx->items as $item)
                            {{ $item->product->name ?? '-' }} ×{{ $item->quantity }}
                            @if(!$loop->last), @endif
                        @endforeach
                    </td>
                    <td>Rp{{ number_format($trx->total_price, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align:center;">Tidak ada transaksi</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">Total Pendapatan</td>
                <td>{{ $formattedTotal }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
