@extends('layouts.admin', ['title' => 'Bonus bulanan'])
@section('content')
<div class="toolbar">
  <form method="get" class="filters">
    <select name="bulan" onchange="this.form.submit()">
      @foreach (month_options() as $val => $label)<option value="{{ $val }}" @selected($val === $month)>{{ $label }}</option>@endforeach
    </select>
  </form>
  <a class="btn ghost" href="{{ route('admin.bonus', ['bulan' => $month, 'export' => 'csv']) }}">Unduh CSV</a>
</div>

<div class="stats">
  <div class="stat"><span>Total omzet personal</span><b>{{ rupiah($t['personal']) }}</b></div>
  <div class="stat"><span>Total bonus</span><b>{{ rupiah($t['bonus']) }}</b></div>
  <div class="stat"><span>Total voucher</span><b>{{ rupiah($t['voucher']) }}</b></div>
  <div class="stat"><span>Royalty dibagikan</span><b>{{ rupiah($t['royalty']) }}</b></div>
  <div class="stat accent"><span>Total dibayar</span><b>{{ rupiah($t['total']) }}</b><small>{{ $t['personal'] ? number_format($t['total'] / $t['personal'] * 100, 1, ',', '.') : 0 }}% dari omzet</small></div>
</div>

<details class="rules">
  <summary>Aturan perhitungan yang dipakai</summary>
  <ul>
    <li><b>Personal</b> = total belanja member di bulan ini.</li>
    <li><b>Group</b> = personal + total personal downline {{ $r['group_depth'] > 0 ? 'sampai '.$r['group_depth'].' level' : 'di semua level' }}.</li>
    <li><b>Bonus</b> = {{ $r['bonus_pct'] }}% × group, bila personal ≥ {{ rupiah($r['min_personal']) }}.</li>
    <li><b>Voucher</b> = {{ rupiah($r['voucher_amount']) }}, bila group ≥ {{ rupiah($r['voucher_min_group']) }}.</li>
    <li><b>Royalty</b> = {{ $r['royalty_pct'] }}% × total omzet personal ({{ rupiah($t['royalty_pool']) }}), penerima voucher mendapat bagian sebesar bonusnya ÷ total bonus semua member.</li>
  </ul>
  <p class="small muted">Diambil dari rumus di file Simulasi MPK Januari. Angka bisa diubah di <a href="{{ route('admin.settings') }}">Pengaturan</a>.</p>
</details>

<div class="panel flush">
<table>
  <thead><tr><th>Member</th><th class="r">Downline</th><th class="r">Personal</th><th class="r">Group</th><th class="r">Bonus</th><th class="r">Voucher</th><th class="r">Royalty</th><th class="r">Total</th></tr></thead>
  <tbody>
  @forelse ($rows as $x)
    <tr>
      <td><a href="{{ route('admin.members.edit', $x['id']) }}">{{ $x['name'] }}</a><small class="mono">{{ $x['member_code'] }}</small></td>
      <td class="r">{{ $x['downlines'] }}</td>
      <td class="r">{{ rupiah($x['personal']) }}</td>
      <td class="r">{{ rupiah($x['group']) }}</td>
      @foreach (['bonus', 'voucher', 'royalty'] as $k)
        <td class="r">@if ($x[$k]){{ rupiah($x[$k]) }}@else<span class="muted">-</span>@endif</td>
      @endforeach
      <td class="r"><b>{{ rupiah($x['total']) }}</b></td>
    </tr>
  @empty
    <tr><td colspan="8" class="muted">Belum ada omzet di {{ month_label($month) }}.</td></tr>
  @endforelse
  </tbody>
</table>
</div>
@endsection
