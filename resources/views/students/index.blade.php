@extends('layouts.app')
@section('title','Students')
@section('content')

<div class="flex items-baseline justify-between mb-4">
  <h1 class="text-lg font-semibold">Students</h1>
  <span class="text-sm text-ink-600">{{ number_format($students->total()) }} found</span>
</div>

<form method="GET" class="mb-4 flex gap-2">
  <input name="q" value="{{ request('q') }}" placeholder="Name, admission no. or roll no."
         class="flex-1 rounded-lg border border-ink-100 px-3 py-2 text-sm">
  <select name="status" class="rounded-lg border border-ink-100 px-2 py-2 text-sm bg-white">
    @foreach(['active'=>'Active','passed_out'=>'Passed out','' => 'All'] as $v=>$l)
      <option value="{{ $v }}" @selected(request('status','active')===$v)>{{ $l }}</option>
    @endforeach
  </select>
  <button class="rounded-lg bg-ink-800 text-white px-4 text-sm font-medium">Search</button>
</form>

<div class="bg-white rounded-xl border border-ink-100 overflow-hidden">
  <ul class="divide-y divide-ink-100">
    @forelse($students as $s)
      <li class="px-4 py-3 flex items-center gap-3">
        <span class="min-w-0 flex-1">
          <span class="block font-medium truncate">{{ $s->first_name }} {{ $s->last_name }}</span>
          <span class="block text-xs text-ink-600">
            {{ $s->admission_no }}
            @if($s->currentEnrollment?->batch) · {{ $s->currentEnrollment->batch->name }} @endif
          </span>
        </span>
        @if($s->status !== 'active')
          <span class="text-xs rounded-full bg-ink-50 px-2 py-0.5 text-ink-600">{{ str_replace('_',' ',$s->status) }}</span>
        @endif
        @if($s->phone)<span class="hidden sm:block text-xs text-ink-600 tabular-nums">{{ $s->phone }}</span>@endif
      </li>
    @empty
      <li class="px-4 py-8 text-center text-sm text-ink-600">No students match.</li>
    @endforelse
  </ul>
</div>

<div class="mt-4">{{ $students->withQueryString()->links() }}</div>

@endsection
