@extends('layouts.app')

@section('title', 'Tambah Kategori')
@section('page_title', 'Tambah Kategori Menu')

@section('content')
<div style="max-width: 600px;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Form Tambah Kategori</h2>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
        </div>

        <form action="{{ route('admin.categories.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label" for="nama_kategori">Nama Kategori <span style="color: var(--danger);">*</span></label>
                    <input type="text" id="nama_kategori" name="nama_kategori" class="form-control" value="{{ old('nama_kategori') }}" required placeholder="Contoh: Kopi Tradisional, Camilan">
                    @error('nama_kategori')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="card-footer" style="display: flex; justify-content: flex-end; gap: 10px;">
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Kategori</button>
            </div>
        </form>
    </div>
</div>
@endsection
