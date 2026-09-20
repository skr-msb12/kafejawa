@extends('layouts.app')

@section('title', 'Tambah Pengguna')
@section('page_title', 'Tambah Pengguna Baru')

@section('content')
<div style="max-width: 600px;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Form Tambah Akun Pengguna</h2>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
        </div>

        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label" for="name">Nama Lengkap <span style="color: var(--danger);">*</span></label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required placeholder="Nama staf / operator">
                    @error('name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Alamat Email <span style="color: var(--danger);">*</span></label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="staf@kafejawa.local">
                    @error('email')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="role">Peran Sistem (Role) <span style="color: var(--danger);">*</span></label>
                    <select name="role" id="role" class="form-control" required>
                        <option value="kasir" {{ old('role') == 'kasir' ? 'selected' : '' }}>Kasir (Akses Terminal Penjualan & Riwayat)</option>
                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrator (Akses Penuh Master Data & Laporan)</option>
                    </select>
                    @error('role')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Kata Sandi Awal <span style="color: var(--danger);">*</span></label>
                    <input type="password" id="password" name="password" class="form-control" required minlength="6" placeholder="Minimal 6 karakter">
                    @error('password')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="card-footer" style="display: flex; justify-content: flex-end; gap: 10px;">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Daftarkan Pengguna</button>
            </div>
        </form>
    </div>
</div>
@endsection
