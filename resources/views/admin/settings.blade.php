@extends('layouts.admin', ['title' => 'Pengaturan'])
@section('content')
<form method="post" class="form" action="{{ route('admin.settings.update') }}">
  @csrf @method('PUT')
  @foreach ($fields as $group => $items)
    <section class="panel">
      <h2>{{ $group }}</h2>
      @foreach ($items as $key => [$label, $type])
        <label>{{ $label }}
          @if ($type === 'textarea')
            <textarea name="{{ $key }}" rows="5">{{ old($key, setting($key)) }}</textarea>
          @elseif ($type === 'number')
            <input type="text" inputmode="decimal" name="{{ $key }}" value="{{ old($key, setting($key)) }}">
          @else
            <input type="{{ $type }}" name="{{ $key }}" value="{{ old($key, setting($key)) }}">
          @endif
        </label>
      @endforeach
    </section>
  @endforeach
  <div class="actions"><button class="btn">Simpan pengaturan</button></div>
</form>
@endsection
