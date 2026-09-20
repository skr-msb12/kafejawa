@extends('layouts.app')

@section('title', 'Ubah Kategori')
@section('page_title', 'Ubah Kategori Menu')

@section('content')
<div style="max-width: 600px;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Form Ubah Kategori: {{ $category->nama_kategori }}</h2>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
        </div>

        <form action="{{ route('admin.categories.update', $category->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label" for="nama_kategori">Nama Kategori <span style="color: var(--danger);">*</span></label>
                    <input type="text" id="nama_kategori" name="nama_kategori" class="form-control" value="{{ old('nama_kategori', $category->nama_kategori) }}" required>
                    @error('nama_kategori')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="card-footer" style="display: flex; justify-content: flex-end; gap: 10px;">
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
