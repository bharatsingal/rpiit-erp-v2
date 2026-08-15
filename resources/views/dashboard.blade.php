@extends('layouts.app')
@section('title','Dashboard')
@section('content')

<div class="flex items-baseline justify-between mb-5">
  <div>
    <h1 class="text-lg font-semibold">Dashboard</h1>
    <p class="text-sm text-ink-600">{{ $year?->name ? 'Academic year '.$year->name : 'No current academic year set' }}</p>
  </div>
</div>

<!-- headline figures -->
<div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 mb-6">
  @php
    $tiles = [
      ['Students', number_format($students), $passedOut ? number_format($passedOut).' passed out' : null, 'emerald'],
      ['Staff', number_format($staffCount), number_format($supportCount).' support staff', 'sky'],
      ['Courses', number_format($courses), null, 'violet'],
      ['Students with dues', number_format($duesCount),
        $feesAsOf ? '₹'.number_format($duesTotal).' outstanding' : 'No fee data imported', 'rose'],
    ];
  @endphp
  @foreach($tiles as [$label, $value, $sub, $tone])
    <div class="bg-white rounded-xl border border-ink-100 p-4 flex items-start gap-3">
      @php $bar = ['emerald'=>'bg-emerald-500','sky'=>'bg-sky-500','violet'=>'bg-violet-500','rose'=>'bg-rose-500'][$tone]; @endphp
      <span class="w-1 self-stretch rounded-full {{ $bar }}"></span>
      <span class="min-w-0">
        <span class="block text-[11px] uppercase tracking-wide text-ink-600">{{ $label }}</span>
        <span class="block text-2xl font-semibold mt-0.5 tabular-nums">{{ $value }}</span>
        @if($sub)<span class="block text-xs text-ink-600 mt-0.5">{{ $sub }}</span>@endif
      </span>
    </div>
  @endforeach
</div>

<!-- course cards, the layout the old ERP used -->
@if($byCourse->isNotEmpty())
  <h2 class="text-sm font-semibold mb-3">Active students by course</h2>
  <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
    @foreach($byCourse as $row)
      <div class="bg-white rounded-xl border border-ink-100 p-4 flex items-center gap-3">
        <span class="min-w-0 flex-1">
          <span class="block text-xs text-ink-600 truncate uppercase tracking-wide">{{ $row->name }}</span>
          <span class="block text-2xl font-semibold mt-1 tabular-nums">{{ number_format($row->total) }}</span>
        </span>
        <span class="w-11 h-11 shrink-0 rounded-full bg-ink-50 grid place-items-center">
          <svg class="w-5 h-5 text-ink-600" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 3L2 8l10 5 10-5-10-5zM4 12v5c0 1 4 3 8 3s8-2 8-3v-5"/>
          </svg>
        </span>
      </div>
    @endforeach
  </div>
@else
  <div class="bg-white rounded-xl border border-ink-100 p-6 text-sm text-ink-600">
    No students imported yet.
  </div>
@endif

@endsection
