@extends('layouts.admin', ['title' => 'Omzet / Belanja'])
@section('content')
<section class="panel">
  <h2>Catat belanja member</h2>
  <form method="post" class="inline-form" action="{{ route('admin.purchases.store') }}">
    @csrf
    <input name="member_code" placeholder="Kode member" required autocomplete="off" value="{{ old('member_code') }}">
    <input type="date" name="purchase_date" value="{{ old('purchase_date', now()->toDateString()) }}" required>
    <input name="amount" inputmode="numeric" placeholder="Nominal, mis. 1000000" required value="{{ old('amount') }}">
    <input name="note" placeholder="Keterangan (opsional)" value="{{ old('note') }}">
    <button class="btn">Catat</button>
  </form>
</section>

<div class="toolbar">
  <form method="get" class="filters">
    <select name="bulan" onchange="this.form.submit()">
      @foreach (month_options() as $val => $label)<option value="{{ $val }}" @selected($val === $month)>{{ $label }}</option>@endforeach
    </select>
    <input type="search" name="cari" value="{{ $search }}" placeholder="Cari member…">
    <button class="btn ghost">Tampilkan</button>
  </form>
  <div class="stat inline"><span>Total omzet {{ month_label($month) }}</span><b>{{ rupiah($sum) }}</b></div>
</div>

<div class="panel flush">
<table>
  <thead><tr><th>Tanggal</th><th>Member</th><th>Keterangan</th><th class="r">Nominal</th><th></th></tr></thead>
  <tbody>
  @forelse ($rows as $r)
    <tr>
      <td>{{ $r->purchase_date->format('d/m/Y') }}</td>
      <td><a href="{{ route('admin.members.edit', $r->member_id) }}">{{ $r->member->name }}</a><small class="mono">{{ $r->member->member_code }}</small></td>
      <td>{{ $r->note }}</td>
      <td class="r">{{ rupiah($r->amount) }}</td>
      <td class="r"><form method="post" action="{{ route('admin.purchases.destroy', $r) }}" onsubmit="return confirm('Hapus transaksi ini?')">@csrf @method('DELETE')<button class="link danger small">hapus</button></form></td>
    </tr>
  @empty
    <tr><td colspan="5" class="muted">Belum ada transaksi di bulan ini.</td></tr>
  @endforelse
  </tbody>
</table>
</div>
@include('partials.pager', ['p' => $rows])
@endsection
