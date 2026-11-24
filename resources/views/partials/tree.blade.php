<ul>
@foreach ($nodes as $node)
  @php $k = $node['member']; @endphp
  <li><a href="{{ route('admin.members.edit', $k) }}">{{ $k->name }}</a> <small class="mono">{{ $k->member_code }}</small>
    @unless ($k->isActive()) <span class="badge off">nonaktif</span> @endunless
    @if ($node['children'])
      @include('partials.tree', ['nodes' => $node['children']])
    @elseif ($node['more'])
      <small class="muted">(+{{ $node['more'] }} downline)</small>
    @endif
  </li>
@endforeach
</ul>
