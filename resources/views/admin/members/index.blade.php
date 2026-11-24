@extends('layouts.admin', ['title' => 'Member'])
@section('content')
<div class="toolbar">
  <form method="get" class="filters">
    <input type="search" name="cari" value="{{ $search }}" placeholder="Cari nama, kode, HP, alamat…">
    <select name="status" onchange="this.form.submit()">
      <option value="">Semua status</option>
      <option value="aktif" @selected($status === 'aktif')>Aktif</option>
      <option value="nonaktif" @selected($status === 'nonaktif')>Nonaktif</option>
    </select>
    <button class="btn ghost">Cari</button>
  </form>
  <div>
    <a class="btn ghost" href="{{ request()->fullUrlWithQuery(['export' => 'csv', 'page' => null]) }}">Unduh CSV</a>
    <a class="btn" href="{{ route('admin.members.create') }}">+ Tambah member</a>
  </div>
</div>
<p class="muted small">{{ number_format($members->total(), 0, ',', '.') }} member ditemukan</p>
<div class="panel flush">
<table>
  <thead><tr><th>Kode</th><th>Nama</th><th>No. HP</th><th>Alamat</th><th>Sponsor</th><th class="r">Downline</th><th>Status</th></tr></thead>
  <tbody>
  @forelse ($members as $m)
    <tr>
      <td class="mono">{{ $m->member_code }}</td>
      <td><a href="{{ route('admin.members.edit', $m) }}"><b>{{ $m->name }}</b></a></td>
      <td>@if ($m->phone)<a href="{{ wa_link($m->phone) }}" target="_blank" rel="noopener">{{ $m->phone }}</a>@endif</td>
      <td>{{ $m->address }}</td>
      <td>@if ($m->sponsor)<a href="{{ route('admin.members.edit', $m->sponsor) }}">{{ $m->sponsor->name }}</a>@else<span class="muted">-</span>@endif</td>
      <td class="r">{{ $m->downlines_count }}</td>
      <td><span class="badge {{ $m->isActive() ? 'ok' : 'off' }}">{{ $m->status }}</span></td>
    </tr>
  @empty
    <tr><td colspan="7" class="muted">Tidak ada member.</td></tr>
  @endforelse
  </tbody>
</table>
</div>
@include('partials.pager', ['p' => $members])
@endsection
