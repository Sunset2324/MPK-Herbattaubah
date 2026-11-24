@if ($p->lastPage() > 1)
<nav class="pager">
  @if (! $p->onFirstPage())<a href="{{ $p->previousPageUrl() }}">‹ Sebelumnya</a>@endif
  <span>Halaman {{ $p->currentPage() }} dari {{ $p->lastPage() }}</span>
  @if ($p->hasMorePages())<a href="{{ $p->nextPageUrl() }}">Berikutnya ›</a>@endif
</nav>
@endif
