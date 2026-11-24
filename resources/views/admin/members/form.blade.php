@php $isNew = ! $member->exists; @endphp
@extends('layouts.admin', ['title' => $isNew ? 'Tambah member' : $member->name])
@section('content')
<div class="cols">
  <section class="panel">
    <h2>Data member</h2>
    <form method="post" class="form" action="{{ $isNew ? route('admin.members.store') : route('admin.members.update', $member) }}">
      @csrf
      @unless ($isNew) @method('PUT') @endunless
      <div class="row2">
        <label>Kode member<input name="member_code" value="{{ old('member_code', $member->member_code) }}" placeholder="Otomatis bila kosong"></label>
        <label>Status<select name="status">
          <option value="aktif" @selected(old('status', $member->status) === 'aktif')>Aktif</option>
          <option value="nonaktif" @selected(old('status', $member->status) === 'nonaktif')>Nonaktif</option>
        </select></label>
      </div>
      <label>Nama lengkap *<input name="name" required value="{{ old('name', $member->name) }}"></label>
      <div class="row2">
        <label>No. HP / WA<input name="phone" value="{{ old('phone', $member->phone) }}"></label>
        <label>Email<input type="email" name="email" value="{{ old('email', $member->email) }}"></label>
      </div>
      <label>Alamat<input name="address" value="{{ old('address', $member->address) }}"></label>
      <div class="row2">
        <label>Kode sponsor (upline)<input name="sponsor_code" value="{{ old('sponsor_code', $member->sponsor?->member_code) }}" placeholder="mis. MPK0000001">
          @if ($member->sponsor)<small>Saat ini: <a href="{{ route('admin.members.edit', $member->sponsor) }}">{{ $member->sponsor->name }}</a></small>@endif
        </label>
        <label>Tanggal gabung<input type="date" name="joined_at" value="{{ old('joined_at', $member->joined_at?->toDateString()) }}"></label>
      </div>
      <label>Catatan<textarea name="notes" rows="3">{{ old('notes', $member->notes) }}</textarea></label>
      <div class="actions">
        <button class="btn">Simpan</button>
        @if (! $isNew && $member->phone)<a class="btn ghost" target="_blank" rel="noopener" href="{{ wa_link($member->phone, 'Assalamualaikum '.$member->name.', ') }}">Chat WA</a>@endif
      </div>
    </form>
    @unless ($isNew)
    <form method="post" action="{{ route('admin.members.destroy', $member) }}" class="danger-zone" onsubmit="return confirm('Hapus member ini beserta riwayat belanjanya? Downline akan dipindah ke sponsor di atasnya.')">
      @csrf @method('DELETE')
      <button class="link danger">Hapus member</button>
    </form>
    @endunless
  </section>

  @unless ($isNew)
  <div>
    <section class="panel">
      <h2>Jaringan</h2>
      <p class="small muted">Upline: @if ($member->sponsor)<a href="{{ route('admin.members.edit', $member->sponsor) }}">{{ $member->sponsor->name }}</a>@else - @endif · Downline langsung: <b>{{ $directCount }}</b></p>
      <div class="tree">
        @if ($tree) @include('partials.tree', ['nodes' => $tree]) @else <p class="muted small">Belum punya downline.</p> @endif
      </div>
    </section>

    <section class="panel" id="belanja">
      <div class="panel-head"><h2>Belanja / omzet</h2><span class="small">Bulan ini: <b>{{ rupiah($monthTotal) }}</b></span></div>
      <form method="post" class="inline-form" action="{{ route('admin.members.purchases.store', $member) }}">
        @csrf
        <input type="date" name="purchase_date" value="{{ now()->toDateString() }}" required>
        <input name="amount" inputmode="numeric" placeholder="Nominal, mis. 1000000" required>
        <input name="note" placeholder="Keterangan (opsional)">
        <button class="btn">Catat</button>
      </form>
      <table>
        <tbody>
        @forelse ($purchases as $p)
          <tr><td>{{ $p->purchase_date->format('d/m/Y') }}<small>{{ $p->note }}</small></td>
            <td class="r">{{ rupiah($p->amount) }}</td>
            <td class="r"><form method="post" action="{{ route('admin.members.purchases.destroy', [$member, $p]) }}" onsubmit="return confirm('Hapus transaksi ini?')">@csrf @method('DELETE')<button class="link danger small">hapus</button></form></td></tr>
        @empty
          <tr><td class="muted">Belum ada belanja.</td></tr>
        @endforelse
        </tbody>
      </table>
    </section>
  </div>
  @endunless
</div>
@endsection
