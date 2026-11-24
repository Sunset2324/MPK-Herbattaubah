@php $isNew = ! $product->exists; @endphp
@extends('layouts.admin', ['title' => $isNew ? 'Tambah produk' : $product->name])
@section('content')
<div class="cols">
  <section class="panel">
    <form method="post" enctype="multipart/form-data" class="form" action="{{ $isNew ? route('admin.products.store') : route('admin.products.update', $product) }}">
      @csrf
      @unless ($isNew) @method('PUT') @endunless
      <label>Nama produk *<input name="name" required value="{{ old('name', $product->name) }}"></label>
      <div class="row2">
        <label>Kategori<input name="category" list="cats" value="{{ old('category', $product->category) }}">
          <datalist id="cats">@foreach ($cats as $c)<option value="{{ $c }}">@endforeach</datalist></label>
        <label>Urutan tampil<input type="number" name="sort_order" value="{{ old('sort_order', $product->sort_order ?? 0) }}"></label>
      </div>
      <label>Deskripsi singkat<textarea name="description" rows="4">{{ old('description', $product->description) }}</textarea></label>
      <div class="row3">
        <label>Harga member<input name="price_member" inputmode="numeric" value="{{ old('price_member', $product->price_member) }}" placeholder="mis. 80000"></label>
        <label>Harga jual<input name="price_retail" inputmode="numeric" value="{{ old('price_retail', $product->price_retail) }}" placeholder="mis. 90000"></label>
        <label>Luar Jabodetabek<input name="price_outside" inputmode="numeric" value="{{ old('price_outside', $product->price_outside) }}" placeholder="mis. 110000"></label>
      </div>
      <p class="small muted">Website menampilkan harga jual dan harga luar Jabodetabek. Harga member hanya terlihat di admin. Kosongkan harga jual agar tampil "Tanya harga".</p>
      <label>Gambar (JPG/PNG/WEBP, maks 3 MB)<input type="file" name="image" accept="image/jpeg,image/png,image/webp"></label>
      <label class="check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true))> Tampilkan di website</label>
      <div class="actions"><button class="btn">Simpan</button><a class="btn ghost" href="{{ route('admin.products.index') }}">Kembali</a></div>
    </form>
    @unless ($isNew)
    <form method="post" action="{{ route('admin.products.destroy', $product) }}" class="danger-zone" onsubmit="return confirm('Hapus produk ini?')">
      @csrf @method('DELETE')<button class="link danger">Hapus produk</button>
    </form>
    @endunless
  </section>
  <section class="panel">
    <h2>Gambar saat ini</h2>
    <img class="preview" src="{{ $product->imageUrl() }}" alt="" onerror="this.onerror=null;this.src='{{ asset('assets/img/no-image.svg') }}'">
    @if ($product->isHotlinked())
      <p class="small muted">Gambar ini masih diambil dari website lama. Unggah ulang supaya tidak hilang saat website lama dimatikan.</p>
    @endif
  </section>
</div>
@endsection
