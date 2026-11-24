@extends('layouts.admin', ['title' => 'Produk'])
@section('content')
<div class="toolbar">
  <p class="muted small">{{ $products->count() }} produk. Produk nonaktif tidak tampil di website.</p>
  <a class="btn" href="{{ route('admin.products.create') }}">+ Tambah produk</a>
</div>
<div class="panel flush">
<table>
  <thead><tr><th></th><th>Nama</th><th>Kategori</th><th class="r">Harga member</th><th class="r">Harga jual</th><th class="r">Luar Jabodetabek</th><th>Status</th><th class="r">Urutan</th></tr></thead>
  <tbody>
  @foreach ($products as $p)
    <tr>
      <td><img class="thumb" src="{{ $p->imageUrl() }}" alt="" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('assets/img/no-image.svg') }}'"></td>
      <td><a href="{{ route('admin.products.edit', $p) }}"><b>{{ $p->name }}</b></a></td>
      <td>{{ $p->category }}</td>
      @foreach (['price_member', 'price_retail', 'price_outside'] as $k)
        <td class="r">@if ($p->$k){{ rupiah($p->$k) }}@else<span class="muted">-</span>@endif</td>
      @endforeach
      <td><span class="badge {{ $p->is_active ? 'ok' : 'off' }}">{{ $p->is_active ? 'tampil' : 'disembunyikan' }}</span></td>
      <td class="r">{{ $p->sort_order }}</td>
    </tr>
  @endforeach
  </tbody>
</table>
</div>
@endsection
