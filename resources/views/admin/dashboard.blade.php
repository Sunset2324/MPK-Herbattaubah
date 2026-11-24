@extends('layouts.admin', ['title' => 'Dashboard'])
@section('content')
<div class="stats">
  @foreach ($stats as $label => $val)
    <div class="stat"><span>{{ $label }}</span><b>{{ $val }}</b></div>
  @endforeach
</div>

<div class="cols">
  <section class="panel">
    <div class="panel-head"><h2>Peringkat bonus {{ month_label($month) }}</h2><a href="{{ route('admin.bonus') }}">Lihat semua</a></div>
    <table>
      <thead><tr><th>Member</th><th class="r">Personal</th><th class="r">Group</th><th class="r">Total bonus</th></tr></thead>
      <tbody>
      @forelse ($top as $r)
        <tr><td><a href="{{ route('admin.members.edit', $r['id']) }}">{{ $r['name'] }}</a><small>{{ $r['member_code'] }}</small></td>
          <td class="r">{{ rupiah($r['personal']) }}</td><td class="r">{{ rupiah($r['group']) }}</td><td class="r"><b>{{ rupiah($r['total']) }}</b></td></tr>
      @empty
        <tr><td colspan="4" class="muted">Belum ada omzet bulan ini.</td></tr>
      @endforelse
      </tbody>
    </table>
  </section>
  <section class="panel">
    <div class="panel-head"><h2>Belanja terbaru</h2><a href="{{ route('admin.purchases.index') }}">Catat belanja</a></div>
    <table>
      <tbody>
      @forelse ($recent as $r)
        <tr><td>{{ $r->member->name }}<small>{{ $r->purchase_date->format('d/m/Y') }}</small></td><td class="r">{{ rupiah($r->amount) }}</td></tr>
      @empty
        <tr><td class="muted">Belum ada data.</td></tr>
      @endforelse
      </tbody>
    </table>
    <div class="panel-head" style="margin-top:1.5rem"><h2>Member terbaru</h2><a href="{{ route('admin.members.create') }}">+ Tambah</a></div>
    <table>
      <tbody>
      @foreach ($newMembers as $m)
        <tr><td><a href="{{ route('admin.members.edit', $m) }}">{{ $m->name }}</a><small>Sponsor: {{ $m->sponsor?->name ?? '-' }}</small></td><td class="r small muted">{{ $m->joined_at?->format('d/m/Y') }}</td></tr>
      @endforeach
      </tbody>
    </table>
  </section>
</div>
@endsection
