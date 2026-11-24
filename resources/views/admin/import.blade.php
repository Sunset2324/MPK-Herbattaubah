@extends('layouts.admin', ['title' => 'Impor data'])
@section('content')
@if ($report = session('report'))
  <div class="flash ok"><b>{{ $report[0] }}</b>
    @if (count($report) > 1)<ul class="small">@foreach (array_slice($report, 1, 50) as $line)<li>{{ $line }}</li>@endforeach</ul>@endif
  </div>
@endif
<div class="cols">
  <section class="panel">
    <h2>Impor member</h2>
    <p class="small muted">File CSV dengan kolom: <code>kode, nama, hp, email, alamat, kode_sponsor, status, tgl_gabung</code>. Bisa disimpan dari Excel lewat "Save As → CSV". Member dengan kode yang sudah ada akan diperbarui.</p>
    <form method="post" enctype="multipart/form-data" class="form" action="{{ route('admin.import.store') }}">
      @csrf<input type="hidden" name="type" value="members">
      <input type="file" name="file" accept=".csv,text/csv" required>
      <button class="btn">Impor member</button>
    </form>
  </section>
  <section class="panel">
    <h2>Impor belanja / omzet</h2>
    <p class="small muted">File CSV dengan kolom: <code>kode, tanggal, nominal, keterangan</code>. Tanggal format 2026-01-15 atau 15/01/2026.</p>
    <form method="post" enctype="multipart/form-data" class="form" action="{{ route('admin.import.store') }}">
      @csrf<input type="hidden" name="type" value="purchases">
      <input type="file" name="file" accept=".csv,text/csv" required>
      <button class="btn">Impor belanja</button>
    </form>
  </section>
</div>
@endsection
