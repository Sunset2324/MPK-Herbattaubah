@extends('layouts.admin', ['title' => 'Akun admin'])
@section('content')
<div class="cols">
  <section class="panel">
    <h2>Daftar admin</h2>
    <table>
      <tbody>
      @foreach ($users as $u)
        <tr><td><b>{{ $u->name }}</b><small>{{ $u->email }}</small></td>
          <td class="r">
            @if ($u->is(auth()->user()))
              <span class="badge ok">Anda</span>
            @else
              <form method="post" action="{{ route('admin.users.destroy', $u) }}" onsubmit="return confirm('Hapus akun admin ini?')">@csrf @method('DELETE')<button class="link danger small">hapus</button></form>
            @endif
          </td></tr>
      @endforeach
      </tbody>
    </table>
    <h2 style="margin-top:1.5rem">Tambah admin</h2>
    <form method="post" class="form" action="{{ route('admin.users.store') }}">
      @csrf
      <label>Nama<input name="name" required value="{{ old('name') }}"></label>
      <label>Email<input type="email" name="email" required value="{{ old('email') }}"></label>
      <label>Password (min. 8 karakter)<input type="password" name="password" required minlength="8"></label>
      <button class="btn">Tambah</button>
    </form>
  </section>
  <section class="panel">
    <h2>Ganti password saya</h2>
    <form method="post" class="form" action="{{ route('admin.users.password') }}">
      @csrf @method('PUT')
      <label>Password lama<input type="password" name="current" required></label>
      <label>Password baru<input type="password" name="new" required minlength="8"></label>
      <button class="btn">Ganti password</button>
    </form>
  </section>
</div>
@endsection
