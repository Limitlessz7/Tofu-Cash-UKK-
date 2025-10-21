<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 40px;
            color: #333;
        }
        h1 {
            text-align: center;
            margin-bottom: 10px;
            font-size: 20px;
            color: #222;
        }
        .subtitle {
            text-align: center;
            margin-bottom: 20px;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: left;
        }
        th {
            background: #f5f5f5;
        }
        tfoot td {
            font-weight: bold;
            background: #f5f5f5;
        }
        .total {
            text-align: right;
            margin-top: 15px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="subtitle">Periode: {{ $rangeLabel }}</div>

    <table>
        <thead>
            <tr>
                <th style="width: 10%;">ID</th>
                <th style="width: 40%;">Tanggal</th>
                <th style="width: 50%;">Total (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $trx)
                <tr>
                    <td>{{ $trx->id }}</td>
                    <td>{{ \Carbon\Carbon::parse($trx->transaction_date)->format('d-m-Y H:i') }}</td>
                    <td>Rp{{ number_format($trx->total, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center;">Tidak ada transaksi</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Total Pendapatan</td>
                <td>Rp{{ number_format($total, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="total">
        Dicetak pada: {{ now()->format('d-m-Y H:i') }}
    </div>
</body>
</html>
