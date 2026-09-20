@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Ringkasan Dashboard')

@section('content')
<div class="grid-cols-4" style="margin-bottom: 24px;">
    <div class="stat-box">
        <div class="stat-info">
            <span class="stat-label">Pendapatan Hari Ini</span>
            <span class="stat-value">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</span>
        </div>
        <div class="stat-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
        </div>
    </div>

    <div class="stat-box">
        <div class="stat-info">
            <span class="stat-label">Transaksi Hari Ini</span>
            <span class="stat-value">{{ $todayTransactionsCount }}</span>
        </div>
        <div class="stat-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
        </div>
    </div>

    <div class="stat-box">
        <div class="stat-info">
            <span class="stat-label">Menu Aktif</span>
            <span class="stat-value">{{ $activeProductsCount }}</span>
        </div>
        <div class="stat-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line></svg>
        </div>
    </div>

    <div class="stat-box">
        <div class="stat-info">
            <span class="stat-label">Peringatan Stok Rendah</span>
            <span class="stat-value" style="color: {{ $lowStockProducts->count() > 0 ? '#dc2626' : '#16a34a' }};">
                {{ $lowStockProducts->count() }}
            </span>
        </div>
        <div class="stat-icon" style="color: {{ $lowStockProducts->count() > 0 ? '#dc2626' : '#16a34a' }};">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
        </div>
    </div>
</div>

<div class="grid-cols-2">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Peringatan Stok Menipis (&le; 10 Porsi)</h2>
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary btn-sm">Kelola Produk</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Menu</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Sisa Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($lowStockProducts as $low)
                            <tr>
                                <td style="font-weight: 600;">{{ $low->nama_menu }}</td>
                                <td>{{ $low->category ? $low->category->nama_kategori : '-' }}</td>
                                <td>Rp {{ number_format($low->harga, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge {{ $low->stok <= 5 ? 'badge-danger' : 'badge-warning' }}">
                                        {{ $low->stok }} sisa
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 24px;">
                                    Semua stok produk dalam kondisi aman.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Transaksi Terakhir</h2>
            <a href="{{ route('pos.history') }}" class="btn btn-secondary btn-sm">Lihat Semua</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No Invoice</th>
                            <th>Kasir</th>
                            <th>Waktu</th>
                            <th>Total</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentTransactions as $trx)
                            <tr>
                                <td style="font-weight: 700; color: var(--accent);">{{ $trx->no_invoice }}</td>
                                <td>{{ $trx->user ? $trx->user->name : '-' }}</td>
                                <td>{{ $trx->tanggal ? $trx->tanggal->format('H:i d/m') : '-' }}</td>
                                <td style="font-weight: 600;">Rp {{ number_format($trx->total_bayar, 0, ',', '.') }}</td>
                                <td>
                                    <a href="{{ route('pos.receipt', $trx->id) }}" class="btn btn-secondary btn-sm" target="_blank">
                                        Struk
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 24px;">
                                    Belum ada transaksi tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
