<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan KafeJawa - {{ strtoupper($period) }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body {
            background-color: #ffffff;
            color: #000000;
            padding: 20px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div style="max-width: 900px; margin: 0 auto;">
        <div class="no-print" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <a href="{{ route('admin.reports.index', ['period' => $period]) }}" class="btn btn-secondary btn-sm">Kembali ke Laporan</a>
            <button onclick="window.print()" class="btn btn-primary btn-sm">Cetak Dokumen Sekarang</button>
        </div>

        <div style="border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: flex-end;">
            <div>
                <h1 style="font-size: 20px; font-weight: 800; color: #0f172a;">KAFEJAWA POS - LAPORAN PENJUALAN</h1>
                <p style="font-size: 12px; color: #64748b;">
                    Periode: <strong>{{ strtoupper(str_replace('_', ' ', $period)) }}</strong>
                    @if ($startDate && $endDate)
                        ({{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }})
                    @endif
                </p>
            </div>
            <div style="text-align: right; font-size: 11px; color: #64748b;">
                <div>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</div>
                <div>Oleh: {{ auth()->user()->name }}</div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 24px;">
            <div style="border: 1px solid #cbd5e1; padding: 10px 14px; border-radius: var(--radius);">
                <div style="font-size: 11px; color: #64748b; text-transform: uppercase;">Total Omset</div>
                <div style="font-size: 18px; font-weight: 800; color: #78350f;">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
            </div>
            <div style="border: 1px solid #cbd5e1; padding: 10px 14px; border-radius: var(--radius);">
                <div style="font-size: 11px; color: #64748b; text-transform: uppercase;">Total Transaksi</div>
                <div style="font-size: 18px; font-weight: 800;">{{ $totalTransactions }}</div>
            </div>
            <div style="border: 1px solid #cbd5e1; padding: 10px 14px; border-radius: var(--radius);">
                <div style="font-size: 11px; color: #64748b; text-transform: uppercase;">Porsi Terjual</div>
                <div style="font-size: 18px; font-weight: 800;">{{ $totalItemsSold }}</div>
            </div>
            <div style="border: 1px solid #cbd5e1; padding: 10px 14px; border-radius: var(--radius);">
                <div style="font-size: 11px; color: #64748b; text-transform: uppercase;">Rata-rata/Trx</div>
                <div style="font-size: 18px; font-weight: 800;">Rp {{ number_format($averageTransaction, 0, ',', '.') }}</div>
            </div>
        </div>

        <h3 style="font-size: 14px; margin-bottom: 8px;">Daftar Transaksi</h3>
        <table class="table" style="margin-bottom: 30px; border: 1px solid #e2e8f0;">
            <thead>
                <tr>
                    <th>No Invoice</th>
                    <th>Waktu</th>
                    <th>Kasir</th>
                    <th>Metode</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transactions as $trx)
                    <tr>
                        <td style="font-weight: 700;">{{ $trx->no_invoice }}</td>
                        <td>{{ $trx->tanggal ? $trx->tanggal->format('d/m/Y H:i') : '-' }}</td>
                        <td>{{ $trx->user ? $trx->user->name : '-' }}</td>
                        <td style="text-transform: uppercase;">{{ $trx->metode_bayar }}</td>
                        <td style="text-align: right; font-weight: 700;">Rp {{ number_format($trx->total_bayar, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="display: flex; justify-content: flex-end; margin-top: 40px;">
            <div style="text-align: center; width: 200px;">
                <div style="font-size: 12px; margin-bottom: 60px;">Penanggung Jawab,</div>
                <div style="border-bottom: 1px solid #000000; font-weight: 700;">{{ auth()->user()->name }}</div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">{{ auth()->user()->role }}</div>
            </div>
        </div>
    </div>
</body>
</html>
