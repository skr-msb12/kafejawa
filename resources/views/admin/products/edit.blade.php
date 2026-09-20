@extends('layouts.app')

@section('title', 'Ubah Menu')
@section('page_title', 'Ubah Data Menu')

@section('content')
<div style="max-width: 700px;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Form Ubah Menu: {{ $product->nama_menu }}</h2>
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
        </div>

        <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-row">
                    <div class="form-col form-group">
                        <label class="form-label" for="category_id">Kategori Menu <span style="color: var(--danger);">*</span></label>
                        <select name="category_id" id="category_id" class="form-control" required>
                            <option value="">Pilih Kategori</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-col form-group">
                        <label class="form-label" for="nama_menu">Nama Menu <span style="color: var(--danger);">*</span></label>
                        <input type="text" id="nama_menu" name="nama_menu" class="form-control" value="{{ old('nama_menu', $product->nama_menu) }}" required>
                        @error('nama_menu')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col form-group">
                        <label class="form-label" for="harga">Harga Jual (Rp) <span style="color: var(--danger);">*</span></label>
                        <input type="number" id="harga" name="harga" class="form-control" value="{{ old('harga', (int)$product->harga) }}" min="0" step="500" required>
                        @error('harga')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-col form-group">
                        <label class="form-label" for="stok">Stok Tersedia (Porsi) <span style="color: var(--danger);">*</span></label>
                        <input type="number" id="stok" name="stok" class="form-control" value="{{ old('stok', $product->stok) }}" min="0" required>
                        @error('stok')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="gambar">Perbarui Foto Menu (Opsional)</label>
                    @if ($product->gambar)
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px; padding: 8px; background: #f8fafc; border: 1px solid var(--border); border-radius: var(--radius);">
                            <img src="{{ asset('storage/' . $product->gambar) }}" alt="{{ $product->nama_menu }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: var(--radius);">
                            <span style="font-size: 12px; color: var(--text-muted);">Foto saat ini tersimpan di sistem. Unggah file baru di bawah ini untuk menggantinya.</span>
                        </div>
                    @endif
                    <input type="file" id="gambar" name="gambar" class="form-control" accept="image/jpeg,image/png,image/jpg,image/webp">
                    @error('gambar')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group" style="margin-top: 8px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }} style="accent-color: var(--accent);">
                        <span style="font-weight: 600; font-size: 13px;">Status Menu Aktif (Tampil di Terminal POS)</span>
                    </label>
                </div>
            </div>

            <div class="card-footer" style="display: flex; justify-content: flex-end; gap: 10px;">
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
