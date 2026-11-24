<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>{{ $title }} · Admin MPK</title>
<link rel="stylesheet" href="{{ asset('assets/admin.css') }}">
</head>
<body>
@php
  $menu = [
    'admin.dashboard' => ['admin.dashboard', 'Dashboard'],
    'admin.members.*' => ['admin.members.index', 'Member'],
    'admin.purchases.*' => ['admin.purchases.index', 'Omzet / Belanja'],
    'admin.bonus' => ['admin.bonus', 'Bonus Bulanan'],
    'admin.products.*' => ['admin.products.index', 'Produk'],
    'admin.import*' => ['admin.import', 'Impor Data'],
    'admin.settings*' => ['admin.settings', 'Pengaturan'],
    'admin.users*' => ['admin.users', 'Akun Admin'],
  ];
@endphp
<div class="shell">
  <aside class="side">
    <a class="logo" href="{{ route('admin.dashboard') }}"><span>@include('partials.leaf', ['size' => 18])</span> Admin MPK</a>
    <nav>
      @foreach ($menu as $pattern => [$routeName, $label])
        <a class="{{ request()->routeIs($pattern) ? 'on' : '' }}" href="{{ route($routeName) }}">{{ $label }}</a>
      @endforeach
    </nav>
    <div class="side-foot">
      <a href="{{ route('home') }}" target="_blank">Lihat website ↗</a>
      <form method="post" action="{{ route('admin.logout') }}">@csrf<button class="link">Keluar ({{ auth()->user()->name }})</button></form>
    </div>
  </aside>
  <main class="main">
    <header class="top"><h1>{{ $title }}</h1></header>
    @if (session('ok'))<div class="flash ok">{{ session('ok') }}</div>@endif
    @if (session('err'))<div class="flash err">{{ session('err') }}</div>@endif
    @if ($errors->any())<div class="flash err">{!! implode('<br>', array_map('e', $errors->all())) !!}</div>@endif
    @yield('content')
  </main>
</div>
</body>
</html>
