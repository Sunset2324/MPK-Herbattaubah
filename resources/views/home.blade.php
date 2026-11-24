<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $siteName }} | {{ setting('long_name') }}</title>
<meta name="description" content="{{ mb_substr(setting('about'), 0, 155) }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/site.css') }}">
</head>
<body>

<header class="nav" id="top">
  <div class="wrap nav-in">
    <a class="brand" href="#top">
      <span class="brand-mark">@include('partials.leaf')</span>
      <span><b>{{ $siteName }}</b><small>{{ setting('long_name') }}</small></span>
    </a>
    <button class="nav-toggle" aria-label="Buka menu" onclick="document.body.classList.toggle('menu-open')">☰</button>
    <nav class="nav-links">
      <a href="#tentang">Tentang</a>
      <a href="#produk">Produk</a>
      <a href="#pelatihan">Pelatihan</a>
      <a href="#mitra">Jadi Mitra</a>
      <a href="#kontak">Kontak</a>
      <a class="btn btn-sm" href="{{ $waOrder }}" target="_blank" rel="noopener">Pesan via WhatsApp</a>
    </nav>
  </div>
</header>

<section class="hero">
  <div class="wrap hero-in">
    <div class="hero-text">
      <p class="eyebrow">{{ setting('long_name') }}</p>
      <h1>Hidup sehat ala islami, <em>bertumbuh</em> bersama.</h1>
      <p class="lead">Produk herbal pilihan dan pelatihan penyembuhan ala sehat islami dari {{ $siteName }}. {{ setting('tagline') }}.</p>
      <div class="hero-cta">
        <a class="btn" href="#produk">Lihat Produk</a>
        <a class="btn btn-ghost" href="{{ $waJoin }}" target="_blank" rel="noopener">Gabung Jadi Mitra</a>
      </div>
      <ul class="hero-stats">
        <li><b>{{ $totalProducts }}+</b><span>produk herbal</span></li>
        @if ($memberCount >= 20)
        <li><b>{{ number_format($memberCount, 0, ',', '.') }}</b><span>mitra aktif</span></li>
        @endif
        <li><b>BPOM</b><span>produk bersertifikat</span></li>
      </ul>
    </div>
    <div class="hero-art" aria-hidden="true">
      <div class="arch">
        @foreach ($heroProducts as $i => $p)
          <img class="arch-img a{{ $i }}" src="{{ $p->imageUrl() }}" alt="" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('assets/img/no-image.svg') }}'">
        @endforeach
      </div>
    </div>
  </div>
</section>

<section class="section" id="tentang">
  <div class="wrap about">
    <div>
      <p class="eyebrow">Tentang Kami</p>
      <h2>Bisnis yang menyehatkan, berkah untuk banyak keluarga.</h2>
    </div>
    <div>
      @foreach (preg_split('/\n\s*\n/', setting('about')) as $para)
        <p>{!! nl2br(e($para)) !!}</p>
      @endforeach
    </div>
  </div>
  <div class="wrap pillars">
    <article>
      <span class="pill-ic">🌿</span>
      <h3>Multi produk</h3>
      <p>Madu, minyak herbal, minuman sehat hingga skincare. Semua sudah teruji manfaatnya.</p>
    </article>
    <article>
      <span class="pill-ic">✔︎</span>
      <h3>Bersertifikat</h3>
      <p>Produk yang baik adalah produk yang telah melalui uji dan terdaftar di BPOM.</p>
    </article>
    <article>
      <span class="pill-ic">🤝</span>
      <h3>Peluang usaha</h3>
      <p>Jadi mitra MPK dan dapatkan penghasilan dari penjualan pribadi dan jaringan.</p>
    </article>
  </div>
</section>

<section class="section section-alt" id="produk">
  <div class="wrap">
    <div class="section-head">
      <div>
        <p class="eyebrow">Produk</p>
        <h2>Daftar produk {{ $siteName }}</h2>
      </div>
      <form class="search" method="get" action="#produk">
        @if ($cat !== '')<input type="hidden" name="kategori" value="{{ $cat }}">@endif
        <input type="search" name="cari" value="{{ $search }}" placeholder="Cari produk…" aria-label="Cari produk">
      </form>
    </div>
    <div class="chips">
      <a class="chip {{ $cat === '' ? 'on' : '' }}" href="{{ route('home') }}#produk">Semua</a>
      @foreach ($categories as $c)
        <a class="chip {{ $cat === $c->category ? 'on' : '' }}" href="{{ route('home', ['kategori' => $c->category]) }}#produk">{{ $c->category }} <small>{{ $c->n }}</small></a>
      @endforeach
    </div>

    @if ($products->isEmpty())
      <p class="empty">Produk tidak ditemukan.</p>
    @endif
    <div class="grid">
      @foreach ($products as $p)
        <article class="card" id="p-{{ $p->slug }}">
          <button class="card-img" type="button" onclick="openImg(this)" data-full="{{ $p->imageUrl() }}" aria-label="Perbesar gambar {{ $p->name }}">
            <img src="{{ $p->imageUrl() }}" alt="{{ $p->name }}" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('assets/img/no-image.svg') }}'">
          </button>
          <div class="card-body">
            <span class="tag">{{ $p->category }}</span>
            <h3>{{ $p->name }}</h3>
            @if ($p->description)<p>{{ $p->description }}</p>@endif
            <div class="price">
              @if ($p->price_retail)
                <b>{{ rupiah($p->price_retail) }}</b>
                @if ($p->price_outside && $p->price_outside != $p->price_retail)<small>Luar Jabodetabek {{ rupiah($p->price_outside) }}</small>@endif
              @else
                <span class="muted">Tanya harga</span>
              @endif
            </div>
            <a class="btn btn-sm btn-block" target="_blank" rel="noopener" href="{{ $wa ? wa_link($wa, 'Assalamualaikum, saya ingin pesan '.$p->name.'.') : '#kontak' }}">Pesan</a>
          </div>
        </article>
      @endforeach
    </div>
  </div>
</section>

<section class="section" id="pelatihan">
  <div class="wrap split">
    <div class="training-card">
      <p class="eyebrow light">Pelatihan</p>
      <h2>Pelatihan penyembuhan ala sehat islami</h2>
      <p>{!! nl2br(e(setting('training'))) !!}</p>
      <a class="btn btn-gold" href="{{ $waTraining }}" target="_blank" rel="noopener">Tanya jadwal pelatihan</a>
    </div>
    <ol class="steps">
      <li><b>Daftar</b><span>Hubungi admin lewat WhatsApp untuk jadwal dan tempat.</span></li>
      <li><b>Ikuti pelatihan</b><span>Materi pola hidup sehat islami, herbal, dan praktik terapi.</span></li>
      <li><b>Amalkan &amp; bagikan</b><span>Terapkan untuk keluarga, atau jadikan bekal usaha bersama MPK.</span></li>
    </ol>
  </div>
</section>

<section class="section section-alt" id="mitra">
  <div class="wrap split">
    <div>
      <p class="eyebrow">Bisnis Plan</p>
      <h2>Modal kecil, penghasilan besar.</h2>
      <p>MPK memberikan kesempatan penghasilan kepada para mitra yang menjalankan program kami. Kami siapkan produk berkualitas yang dibutuhkan masyarakat Indonesia, Anda tinggal berbagi manfaatnya.</p>
      <a class="btn" href="{{ $waJoin }}" target="_blank" rel="noopener">Daftar jadi mitra</a>
    </div>
    <ul class="benefits">
      <li><b>Bonus belanja</b><span>Dari omzet pribadi dan grup setiap bulan.</span></li>
      <li><b>Voucher</b><span>Untuk mitra yang omzet grupnya mencapai target.</span></li>
      <li><b>Royalti</b><span>Bagi hasil dari omzet seluruh jaringan untuk mitra berprestasi.</span></li>
    </ul>
  </div>
</section>

<section class="section contact" id="kontak">
  <div class="wrap split">
    <div>
      <p class="eyebrow light">Kontak</p>
      <h2>Ada pertanyaan? Kami siap membantu.</h2>
      <ul class="contact-list">
        <li><span>Alamat</span>{{ setting('address') }}</li>
        @if (setting('whatsapp'))<li><span>WhatsApp</span><a href="{{ $waOrder }}" target="_blank" rel="noopener">{{ setting('whatsapp') }}</a></li>@endif
        @if (setting('phone'))<li><span>Telepon</span><a href="tel:{{ preg_replace('/\D/', '', setting('phone')) }}">{{ setting('phone') }}</a></li>@endif
        @if (setting('email'))<li><span>Email</span><a href="mailto:{{ setting('email') }}">{{ setting('email') }}</a></li>@endif
        @if (setting('instagram'))<li><span>Instagram</span><a href="{{ setting('instagram') }}" target="_blank" rel="noopener">{{ setting('instagram') }}</a></li>@endif
        @if (setting('facebook'))<li><span>Facebook</span><a href="{{ setting('facebook') }}" target="_blank" rel="noopener">{{ setting('facebook') }}</a></li>@endif
      </ul>
      <a class="btn btn-gold" href="{{ $waOrder }}" target="_blank" rel="noopener">Chat WhatsApp sekarang</a>
    </div>
    <div class="map">
      @if (setting('maps_embed') && preg_match('~^https://www\.google\.com/maps/embed\?~', setting('maps_embed')))
        <iframe src="{{ setting('maps_embed') }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Lokasi MPK"></iframe>
      @else
        <iframe src="https://maps.google.com/maps?q={{ urlencode(setting('address')) }}&output=embed" loading="lazy" title="Lokasi MPK"></iframe>
      @endif
    </div>
  </div>
</section>

<footer class="foot">
  <div class="wrap foot-in">
    <span>© {{ date('Y') }} {{ $siteName }} · {{ setting('long_name') }}</span>
    <a href="{{ route('admin.dashboard') }}">Masuk admin</a>
  </div>
</footer>

<a class="wa-float" href="{{ $waOrder }}" target="_blank" rel="noopener" aria-label="Chat WhatsApp">
  <svg viewBox="0 0 24 24" width="28" height="28" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8s-.4-.1-.6.1-.7.8-.8 1-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.2-.4.7-1.4.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.8 11.9 11.9 0 0 0 4.6 4c1.7.7 2.4.8 3.2.7a2.8 2.8 0 0 0 1.8-1.3 2.3 2.3 0 0 0 .2-1.3c-.1-.1-.3-.2-.5-.3z"/></svg>
</a>

<div class="lightbox" id="lightbox" onclick="this.classList.remove('on')"><img alt=""></div>
<script>
function openImg(btn){const lb=document.getElementById('lightbox');lb.querySelector('img').src=btn.dataset.full;lb.classList.add('on');}
document.addEventListener('keydown',e=>{if(e.key==='Escape')document.getElementById('lightbox').classList.remove('on')});
document.querySelectorAll('.nav-links a').forEach(a=>a.addEventListener('click',()=>document.body.classList.remove('menu-open')));
</script>
</body>
</html>
