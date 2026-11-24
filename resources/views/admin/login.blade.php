<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Masuk · Admin MPK</title>
<link rel="stylesheet" href="{{ asset('assets/admin.css') }}">
</head>
<body class="login-page">
<form class="login" method="post" action="{{ route('admin.login.attempt') }}">
  <div class="logo big"><span>@include('partials.leaf', ['size' => 18])</span> Admin MPK</div>
  <p class="muted">Masuk untuk mengelola member, produk, dan bonus.</p>
  @error('email')<div class="flash err">{{ $message }}</div>@enderror
  @csrf
  <label>Email<input type="email" name="email" required autofocus value="{{ old('email') }}"></label>
  <label>Password<input type="password" name="password" required></label>
  <label class="check"><input type="checkbox" name="remember" value="1"> Ingat saya</label>
  <button class="btn">Masuk</button>
  <a class="muted small" href="{{ route('home') }}">← Kembali ke website</a>
</form>
</body>
</html>
