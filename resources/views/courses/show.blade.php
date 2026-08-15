@extends('layouts.app')
@section('title', $course->name)
@section('content')

<a href="{{ route('courses.index') }}" class="text-sm text-ink-600">&larr; All courses</a>

<div class="mt-1 mb-5">
  <h1 class="text-lg font-semibold">{{ $course->name }}</h1>
  <p class="text-sm text-ink-600">
    {{ $course->duration_years }} {{ Str::plural('year', $course->duration_years) }} ·
    {{ $course->term_type === 'semester' ? 'Semester system' : 'Annual system' }} ·
    {{ $course->total_terms }} {{ $course->term_type === 'semester' ? 'semesters' : 'years' }}
    @if($course->is_lateral) · lateral entry @endif
  </p>
</div>

<div class="bg-white rounded-xl border border-ink-100 overflow-hidden">
  <div class="px-4 py-3 border-b border-ink-100 text-sm font-medium">Batches</div>
  <ul class="divide-y divide-ink-100">
    @forelse($batches as $b)
      <a href="{{ route('batches.show', $b) }}" class="block hover:bg-ink-50">
        <li class="px-4 py-3 flex items-center gap-3 text-sm">
          <span class="flex-1 min-w-0">
            <span class="block font-medium">{{ $b->name }}</span>
            <span class="block text-xs text-ink-600">
              {{ $b->start_year }}–{{ $b->end_year }}
              @if($b->end_year < now()->year) · completed @endif
              · {{ $b->subject_offerings_count }} {{ Str::plural('subject', $b->subject_offerings_count) }}
            </span>
          </span>
          <span class="text-right">
            <span class="block font-semibold tabular-nums">{{ $b->student_total }}</span>
            <span class="block text-[10px] uppercase tracking-wide text-ink-600">students</span>
          </span>
        </li>
      </a>
    @empty
      <li class="px-4 py-8 text-center text-sm text-ink-600">No batches for this course.</li>
    @endforelse
  </ul>
</div>

@endsection
