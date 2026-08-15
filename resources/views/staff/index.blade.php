@extends('layouts.app')
@section('title','Staff')
@section('content')

<div class="flex items-baseline justify-between mb-4">
  <h1 class="text-lg font-semibold">
    Staff @if($ownDept)<span class="text-ink-600 font-normal">· {{ $ownDept->name }}</span>@endif
  </h1>
  <span class="text-sm text-ink-600">{{ number_format($staff->total()) }} people</span>
</div>

<form method="GET" class="mb-4 flex flex-wrap gap-2">
  <input name="q" value="{{ request('q') }}" placeholder="Name, staff no. or designation"
         class="flex-1 min-w-48 rounded-lg border border-ink-100 px-3 py-2 text-sm">
  @if($departments->count() > 1)
    <select name="department" class="rounded-lg border border-ink-100 px-2 py-2 text-sm bg-white">
      <option value="">All departments</option>
      @foreach($departments as $d)
        <option value="{{ $d->id }}" @selected(request('department') == $d->id)>{{ $d->name }}</option>
      @endforeach
    </select>
  @endif
  <select name="category" class="rounded-lg border border-ink-100 px-2 py-2 text-sm bg-white">
    <option value="">Everyone</option>
    <option value="staff" @selected(request('category')==='staff')>Staff</option>
    <option value="support" @selected(request('category')==='support')>Support staff</option>
  </select>
  <button class="rounded-lg bg-ink-800 text-white px-4 text-sm font-medium">Search</button>
</form>

<div class="bg-white rounded-xl border border-ink-100 overflow-hidden">
  <ul class="divide-y divide-ink-100">
    @forelse($staff as $p)
      <li class="px-4 py-3 flex items-center gap-3 text-sm">
        <span class="min-w-0 flex-1">
          <span class="block font-medium truncate">
            {{ $p->name }}
            @if($p->is_hod)
              <span class="ml-1 text-[10px] uppercase tracking-wide bg-ink-800 text-white rounded px-1.5 py-0.5">HOD</span>
            @endif
          </span>
          <span class="block text-xs text-ink-600">
            {{ $p->staff_no }}
            @if($p->department) · {{ $p->department->name }} @endif
            @if($p->designation) · {{ $p->designation }} @endif
          </span>
        </span>
        @if($p->mobile)
          <a href="tel:{{ $p->mobile }}" class="text-xs text-ink-600 tabular-nums hover:text-ink-900">{{ $p->mobile }}</a>
        @endif
        @if($p->category === 'support')
          <span class="text-[10px] uppercase tracking-wide text-ink-600">support</span>
        @endif
      </li>
    @empty
      <li class="px-4 py-8 text-center text-sm text-ink-600">Nobody matches.</li>
    @endforelse
  </ul>
</div>

<div class="mt-4">{{ $staff->withQueryString()->links() }}</div>

@endsection
