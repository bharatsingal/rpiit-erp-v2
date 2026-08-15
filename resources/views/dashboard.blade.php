@extends('layouts.app')
@section('title','Dashboard')
@section('content')

<h1 class="text-lg font-semibold mb-1">Dashboard</h1>
<p class="text-sm text-ink-600 mb-5">{{ $year?->name ? 'Academic year '.$year->name : 'No current academic year set' }}</p>

<div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 mb-6">
  @php
    $tiles = [
      ['Students', number_format($students), $passedOut ? number_format($passedOut).' passed out' : null],
      ['Staff', number_format($staffCount), number_format($supportCount).' support staff'],
      ['Courses', number_format($courses), null],
      ['Students with dues', number_format($duesCount),
        $feesAsOf ? '₹'.number_format($duesTotal).' outstanding' : 'No fee data imported'],
    ];
  @endphp
  @foreach($tiles as [$label, $value, $sub])
    <div class="bg-white rounded-xl border border-ink-100 p-4">
      <div class="text-xs uppercase tracking-wide text-ink-600">{{ $label }}</div>
      <div class="text-2xl font-semibold mt-1 tabular-nums">{{ $value }}</div>
      @if($sub)<div class="text-xs text-ink-600 mt-0.5">{{ $sub }}</div>@endif
    </div>
  @endforeach
</div>

@if($byCourse->isNotEmpty())
<div class="bg-white rounded-xl border border-ink-100 overflow-hidden">
  <div class="px-4 py-3 border-b border-ink-100 font-medium text-sm">Active students by course</div>
  @php $max = $byCourse->max('total') ?: 1; @endphp
  <ul class="divide-y divide-ink-100">
    @foreach($byCourse as $row)
      <li class="px-4 py-2.5 flex items-center gap-3 text-sm">
        <span class="w-40 shrink-0 truncate">{{ $row->name }}</span>
        <span class="flex-1 h-2 rounded bg-ink-50 overflow-hidden">
          <span class="block h-full bg-ink-600" style="width: {{ round($row->total / $max * 100) }}%"></span>
        </span>
        <span class="w-12 text-right tabular-nums font-medium">{{ $row->total }}</span>
      </li>
    @endforeach
  </ul>
</div>
@else
<div class="bg-white rounded-xl border border-ink-100 p-6 text-sm text-ink-600">
  No students imported yet. Run <code class="bg-ink-50 px-1.5 py-0.5 rounded">php artisan rpiit:import-students</code>.
</div>
@endif

@endsection
