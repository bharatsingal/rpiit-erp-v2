@extends('layouts.app')
@section('title','Attendance')
@section('content')

<h1 class="text-lg font-semibold mb-1">Mark attendance</h1>
<p class="text-sm text-ink-600 mb-5">
  {{ $year?->name ? 'Academic year '.$year->name : 'No current academic year set' }}
</p>

@if($offerings->isEmpty())
  <div class="bg-white rounded-xl border border-ink-100 p-6 text-sm">
    <p class="font-medium mb-1">No subjects are set up yet.</p>
    <p class="text-ink-600">
      Attendance is marked against a subject taught to a batch. Add subjects and
      assign them to batches first — then every class appears here.
    </p>
  </div>
@else
  <div class="grid gap-2 sm:grid-cols-2">
    @foreach($offerings as $o)
      <a href="{{ route('attendance.create', $o) }}"
         class="block bg-white rounded-xl border border-ink-100 p-4 hover:border-ink-600 transition">
        <div class="font-medium">{{ $o->subject?->name ?? 'Subject' }}</div>
        <div class="text-sm text-ink-600 mt-0.5">
          {{ $o->batch?->name }}@if($o->section) · Section {{ $o->section->name }}@endif
        </div>
        @if($o->faculty)
          <div class="text-xs text-ink-600/70 mt-1">{{ $o->faculty->name }}</div>
        @endif
      </a>
    @endforeach
  </div>
@endif

@endsection
