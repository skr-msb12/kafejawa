<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran - {{ $transaction->no_invoice }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="no-print" style="max-width: 480px; margin: 20px auto 0 auto; padding: 0 16px; display: flex; gap: 10px; justify-content: space-between;">
        <a href="{{ route('pos.index') }}" class="btn btn-secondary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            <span>Transaksi Baru</span>
        </a>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('pos.history') }}" class="btn btn-secondary">Riwayat</a>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                <span>Cetak Struk</span>
            </button>
        </div>
    </div>

    <div class="thermal-receipt-container">
        <div class="thermal-receipt">
            <div class="receipt-header">
                <div class="receipt-store-title">KAFEJAWA POS</div>
                <div class="receipt-store-address">
                    Jl. Malioboro No. 45, Yogyakarta<br>
                    Telp: (0274) 554321
                </div>
            </div>

            <div class="receipt-divider"></div>

            <div class="receipt-meta-row">
                <span>No Invoice:</span>
                <strong>{{ $transaction->no_invoice }}</strong>
            </div>
            <div class="receipt-meta-row">
                <span>Tanggal:</span>
                <span>{{ $transaction->tanggal ? $transaction->tanggal->format('d/m/Y H:i:s') : '-' }}</span>
            </div>
            <div class="receipt-meta-row">
                <span>Kasir:</span>
                <span>{{ $transaction->user ? $transaction->user->name : '-' }}</span>
            </div>
            <div class="receipt-meta-row">
                <span>Metode:</span>
                <span style="text-transform: uppercase;">{{ $transaction->metode_bayar }}</span>
            </div>

            <div class="receipt-divider"></div>

            <table class="receipt-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th class="text-right">Qty</th>
                        <th class="text-right">Harga</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transaction->details as $detail)
                        <tr>
                            <td colspan="4" style="font-weight: 700; padding-top: 5px;">
                                {{ $detail->product ? $detail->product->nama_menu : 'Item Menu' }}
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td class="text-right">{{ $detail->jumlah_beli }}x</td>
                            <td class="text-right">{{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                            <td class="text-right" style="font-weight: 600;">{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="receipt-divider"></div>

            <div class="receipt-totals">
                <div class="receipt-total-row">
                    <span>Total Item:</span>
                    <span>{{ $transaction->details->sum('jumlah_beli') }} porsi</span>
                </div>
                <div class="receipt-total-row grand">
                    <span>TOTAL:</span>
                    <span>Rp {{ number_format($transaction->total_bayar, 0, ',', '.') }}</span>
                </div>
                <div class="receipt-total-row">
                    <span>Bayar ({{ strtoupper($transaction->metode_bayar) }}):</span>
                    <span>Rp {{ number_format($transaction->jumlah_bayar, 0, ',', '.') }}</span>
                </div>
                <div class="receipt-total-row">
                    <span>Kembalian:</span>
                    <span>Rp {{ number_format($transaction->kembalian, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="receipt-divider"></div>

            <div class="receipt-footer">
                <div>Terima kasih atas kunjungan Anda!</div>
                <div>Selamat menikmati sajian KafeJawa</div>
                <div style="margin-top: 6px; font-size: 9.5px; color: #475569;">Layanan Pelanggan: halo@kafejawa.local</div>
            </div>
        </div>
    </div>
</body>
</html>
