@extends('layouts.app')

@section('title', 'Riwayat Transaksi')
@section('page_title', 'Riwayat Transaksi Penjualan')

@section('content')
<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; gap: 10px; align-items: center; flex: 1; min-width: 280px;">
            <form method="GET" action="{{ route('pos.history') }}" style="display: flex; gap: 8px; width: 100%; max-width: 400px;">
                <input type="text" name="search" class="form-control" placeholder="Cari no invoice (INV-...)..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-secondary btn-sm">Cari</button>
                @if (request('search'))
                    <a href="{{ route('pos.history') }}" class="btn btn-secondary btn-sm">Reset</a>
                @endif
            </form>
        </div>

        <a href="{{ route('pos.index') }}" class="btn btn-primary btn-sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>Buka Kasir POS</span>
        </a>
    </div>

    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th style="width: 170px;">No Invoice</th>
                        <th>Waktu Transaksi</th>
                        <th>Kasir Bertugas</th>
                        <th>Metode</th>
                        <th>Rincian Item</th>
                        <th style="text-align: right;">Total Bayar</th>
                        <th style="width: 100px; text-align: center;">Struk</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $trx)
                        <tr>
                            <td style="font-weight: 700; color: var(--accent);">{{ $trx->no_invoice }}</td>
                            <td>{{ $trx->tanggal ? $trx->tanggal->format('d/m/Y H:i') : '-' }}</td>
                            <td>{{ $trx->user ? $trx->user->name : '-' }}</td>
                            <td>
                                <span class="badge badge-secondary" style="text-transform: uppercase;">{{ $trx->metode_bayar }}</span>
                            </td>
                            <td>
                                <span class="badge badge-secondary">{{ $trx->details->sum('jumlah_beli') }} porsi</span>
                                <span style="font-size: 11.5px; color: var(--text-muted); margin-left: 6px;">
                                    ({{ $trx->details->pluck('product.nama_menu')->filter()->take(2)->join(', ') }}{{ $trx->details->count() > 2 ? '...' : '' }})
                                </span>
                            </td>
                            <td style="text-align: right; font-weight: 700;">Rp {{ number_format($trx->total_bayar, 0, ',', '.') }}</td>
                            <td style="text-align: center;">
                                <a href="{{ route('pos.receipt', $trx->id) }}" target="_blank" class="btn btn-secondary btn-sm" title="Lihat & Cetak Struk">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                                    <span>Struk</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 24px;">
                                Belum ada riwayat transaksi yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($transactions->hasPages())
        <div class="card-footer">
            {{ $transactions->links() }}
        </div>
    @endif
</div>
@endsection
