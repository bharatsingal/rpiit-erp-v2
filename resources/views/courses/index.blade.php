@extends('layouts.app')
@section('title','Courses')
@section('content')

<h1 class="text-lg font-semibold mb-1">Courses</h1>
<p class="text-sm text-ink-600 mb-5">
  Pick a course, then a batch, then its subjects.
  {{ $year?->name ? 'Academic year '.$year->name : '' }}
</p>

@php
  $labels = [
    'pharmacy' => 'Pharmacy', 'nursing' => 'Nursing', 'paramedical' => 'Paramedical',
    'physiotherapy' => 'Physiotherapy', 'engineering' => 'Engineering',
    'management' => 'Management', 'computer' => 'Computer applications',
    'hotel' => 'Hotel management', 'other' => 'Other',
  ];
  $order = ['pharmacy','nursing','paramedical','physiotherapy','engineering','management','computer','hotel','other'];
@endphp

@foreach($order as $key)
  @continue(!isset($courses[$key]))
  <section class="mb-6">
    <h2 class="text-xs uppercase tracking-wide text-ink-600 mb-2">{{ $labels[$key] ?? ucfirst($key) }}</h2>
    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
      @foreach($courses[$key] as $c)
        <a href="{{ route('courses.show', $c) }}"
           class="bg-white rounded-xl border border-ink-100 p-4 hover:border-ink-600 transition flex items-start gap-3">
          <span class="flex-1 min-w-0">
            <span class="block font-medium truncate">{{ $c->name }}</span>
            <span class="block text-xs text-ink-600 mt-0.5">
              {{ $c->duration_years }} {{ Str::plural('year', $c->duration_years) }} ·
              {{ $c->term_type === 'semester' ? 'Semesters' : 'Annual' }}
              @if($c->is_lateral) · lateral entry @endif
            </span>
            <span class="block text-xs text-ink-600 mt-1">
              {{ $c->batch_total }} {{ Str::plural('batch', $c->batch_total) }}
            </span>
          </span>
          <span class="text-right shrink-0">
            <span class="block text-xl font-semibold tabular-nums leading-none">{{ number_format($c->student_total) }}</span>
            <span class="block text-[10px] uppercase tracking-wide text-ink-600 mt-1">students</span>
          </span>
        </a>
      @endforeach
    </div>
  </section>
@endforeach

@endsection
