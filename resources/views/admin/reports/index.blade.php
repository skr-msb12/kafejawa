@extends('layouts.app')

@section('title', 'Laporan Penjualan')
@section('page_title', 'Laporan Analisis Penjualan')

@section('content')
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.reports.index') }}" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end;">
            <div style="min-width: 160px;">
                <label class="form-label">Periode Waktu</label>
                <select name="period" class="form-control" onchange="toggleCustomDates(this.value)">
                    <option value="hari_ini" {{ $period === 'hari_ini' ? 'selected' : '' }}>Hari Ini</option>
                    <option value="bulan_ini" {{ $period === 'bulan_ini' ? 'selected' : '' }}>Bulan Ini</option>
                    <option value="semua" {{ $period === 'semua' ? 'selected' : '' }}>Semua Transaksi</option>
                    <option value="kustom" {{ $period === 'kustom' ? 'selected' : '' }}>Kustom Rentang Tanggal</option>
                </select>
            </div>

            <div id="customDateRange" style="display: {{ $period === 'kustom' ? 'flex' : 'none' }}; gap: 10px;">
                <div>
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date', $startDate ? $startDate->format('Y-m-d') : '') }}">
                </div>
                <div>
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date', $endDate ? $endDate->format('Y-m-d') : '') }}">
                </div>
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                <a href="{{ request()->fullUrlWithQuery(['print' => 1]) }}" target="_blank" class="btn btn-secondary">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                    <span>Cetak Laporan</span>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="grid-cols-4" style="margin-bottom: 24px;">
    <div class="stat-box">
        <div class="stat-info">
            <span class="stat-label">Total Omset</span>
            <span class="stat-value">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
        </div>
        <div class="stat-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
        </div>
    </div>

    <div class="stat-box">
        <div class="stat-info">
            <span class="stat-label">Jumlah Transaksi</span>
            <span class="stat-value">{{ $totalTransactions }}</span>
        </div>
        <div class="stat-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
        </div>
    </div>

    <div class="stat-box">
        <div class="stat-info">
            <span class="stat-label">Total Porsi Terjual</span>
            <span class="stat-value">{{ $totalItemsSold }}</span>
        </div>
        <div class="stat-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line></svg>
        </div>
    </div>

    <div class="stat-box">
        <div class="stat-info">
            <span class="stat-label">Rata-rata per Transaksi</span>
            <span class="stat-value">Rp {{ number_format($averageTransaction, 0, ',', '.') }}</span>
        </div>
        <div class="stat-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
        </div>
    </div>
</div>

<div class="grid-cols-2" style="margin-bottom: 24px;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Penjualan Berdasarkan Kategori</h2>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Kategori Menu</th>
                            <th style="text-align: right;">Porsi Terjual</th>
                            <th style="text-align: right;">Total Omset</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categoryBreakdown as $cat)
                            <tr>
                                <td style="font-weight: 600;">{{ $cat->nama_kategori }}</td>
                                <td style="text-align: right;">{{ $cat->total_qty }} porsi</td>
                                <td style="text-align: right; font-weight: 700; color: var(--accent);">Rp {{ number_format($cat->total_nominal, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="text-align: center; color: var(--text-muted); padding: 20px;">
                                    Tidak ada data untuk periode ini.
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
            <h2 class="card-title">10 Menu Terlaris</h2>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama Menu</th>
                            <th style="text-align: right;">Jumlah Terjual</th>
                            <th style="text-align: right;">Total Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($productBreakdown as $prod)
                            <tr>
                                <td style="font-weight: 600;">{{ $prod->nama_menu }}</td>
                                <td style="text-align: right;">{{ $prod->total_qty }} porsi</td>
                                <td style="text-align: right; font-weight: 700; color: var(--accent);">Rp {{ number_format($prod->total_nominal, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="text-align: center; color: var(--text-muted); padding: 20px;">
                                    Tidak ada data untuk periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Rincian Transaksi</h2>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No Invoice</th>
                        <th>Waktu Transaksi</th>
                        <th>Kasir Bertugas</th>
                        <th>Metode</th>
                        <th>Total Tagihan</th>
                        <th style="text-align: center;">Struk</th>
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
                            <td style="font-weight: 700;">Rp {{ number_format($trx->total_bayar, 0, ',', '.') }}</td>
                            <td style="text-align: center;">
                                <a href="{{ route('pos.receipt', $trx->id) }}" target="_blank" class="btn btn-secondary btn-sm">Lihat</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 24px;">
                                Tidak ada data transaksi pada periode yang dipilih.
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

<script>
    function toggleCustomDates(value) {
        const container = document.getElementById('customDateRange');
        container.style.display = value === 'kustom' ? 'flex' : 'none';
    }
</script>
@endsection
