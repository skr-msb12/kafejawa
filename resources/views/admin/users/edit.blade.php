@extends('layouts.app')

@section('title', 'Ubah Pengguna')
@section('page_title', 'Ubah Data Pengguna')

@section('content')
<div style="max-width: 600px;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Form Ubah Pengguna: {{ $user->name }}</h2>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
        </div>

        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label" for="name">Nama Lengkap <span style="color: var(--danger);">*</span></label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    @error('name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Alamat Email <span style="color: var(--danger);">*</span></label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                    @error('email')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="role">Peran Sistem (Role) <span style="color: var(--danger);">*</span></label>
                    <select name="role" id="role" class="form-control" required {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                        <option value="kasir" {{ old('role', $user->role) == 'kasir' ? 'selected' : '' }}>Kasir</option>
                        <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Administrator</option>
                    </select>
                    @if ($user->id === auth()->id())
                        <input type="hidden" name="role" value="{{ $user->role }}">
                        <small style="color: var(--text-muted); font-size: 11.5px;">Peran akun Anda sendiri tidak dapat diubah dari sini.</small>
                    @endif
                    @error('role')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Kata Sandi Baru (Kosongkan jika tidak ingin mengubah)</label>
                    <input type="password" id="password" name="password" class="form-control" minlength="6" placeholder="Masukkan sandi baru...">
                    @error('password')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="card-footer" style="display: flex; justify-content: flex-end; gap: 10px;">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
