@extends('layouts.app')
@section('title', $batch->name)
@section('content')

<a href="{{ route('courses.show', $batch->course) }}" class="text-sm text-ink-600">
  &larr; {{ $batch->course->name }}
</a>

<div class="mt-1 mb-5">
  <h1 class="text-lg font-semibold">{{ $batch->name }}</h1>
  <p class="text-sm text-ink-600">
    {{ $batch->start_year }}–{{ $batch->end_year }}
    @if($currentTerm) · currently in <span class="font-medium text-ink-900">{{ $currentTerm->name }}</span>@endif
    · {{ $students->count() }} students
  </p>
</div>

@if($byTerm->count() > 1)
  <div class="mb-5 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-sm">
    <span class="font-medium">Students are split across terms:</span>
    {{ $byTerm->map(fn($r) => $r->term->name.' ('.$r->total.')')->join(', ') }}.
    Usually a batch sits in one term — worth checking with the office.
  </div>
@endif

<div class="grid lg:grid-cols-5 gap-4">

  <div class="lg:col-span-3">
    <div class="bg-white rounded-xl border border-ink-100 overflow-hidden mb-4">
      <div class="px-4 py-3 border-b border-ink-100 text-sm font-medium flex items-center justify-between">
        <span>Subjects</span>
        <a href="{{ route('timetable.show', $batch) }}"
           class="text-xs font-normal rounded-lg bg-ink-50 px-3 py-1.5 hover:bg-ink-100">Timetable</a>
      </div>
      <ul class="divide-y divide-ink-100">
        @forelse($offerings as $o)
          <li class="px-4 py-3 flex items-center gap-3 text-sm">
            <span class="flex-1 min-w-0">
              <span class="block font-medium truncate">
                {{ $o->subject?->code }} — {{ $o->subject?->name }}
              </span>
              <span class="block text-xs text-ink-600">
                {{ $o->term?->name }}@if($o->faculty) · {{ $o->faculty->name }}@endif
                · {{ $o->attendance_sessions_count }} {{ Str::plural('session', $o->attendance_sessions_count) }}
              </span>
            </span>
            <a href="{{ route('attendance.create', $o) }}"
               class="text-xs rounded-lg bg-ink-800 text-white px-3 py-1.5 font-medium">Attendance</a>
          </li>
        @empty
          <li class="px-4 py-8 text-center text-sm text-ink-600">
            No subjects assigned to this batch yet. Add one on the right.
          </li>
        @endforelse
      </ul>
    </div>

    <div class="bg-white rounded-xl border border-ink-100 overflow-hidden">
      <div class="px-4 py-3 border-b border-ink-100 text-sm font-medium flex items-center justify-between">
        <span>Students</span>
        <span class="text-xs text-ink-600 font-normal">{{ $students->count() }}</span>
      </div>
      <ul class="divide-y divide-ink-100 max-h-[28rem] overflow-y-auto">
        @forelse($students as $s)
          <li class="px-4 py-2.5 flex items-center gap-3 text-sm">
            <span class="flex-1 min-w-0 truncate">{{ $s->first_name }} {{ $s->last_name }}</span>
            <span class="text-xs text-ink-600 font-mono">{{ $s->admission_no }}</span>
          </li>
        @empty
          <li class="px-4 py-8 text-center text-sm text-ink-600">No students enrolled this year.</li>
        @endforelse
      </ul>
    </div>
  </div>

  <div class="lg:col-span-2">
    <form method="POST" action="{{ route('offerings.store') }}"
          class="bg-white rounded-xl border border-ink-100 p-4 space-y-3">
      @csrf
      <input type="hidden" name="batch_id" value="{{ $batch->id }}">
      <div class="font-medium text-sm">Add a subject to this batch</div>
      @if($errors->any())<p class="text-xs text-rose-700">{{ $errors->first() }}</p>@endif

      <select name="subject_id" required class="w-full rounded-lg border border-ink-100 px-2 py-2 text-sm bg-white">
        <option value="">Subject…</option>
        @foreach($subjects as $s)
          <option value="{{ $s->id }}">{{ $s->code }} — {{ $s->name }}</option>
        @endforeach
      </select>

      <select name="term_id" required class="w-full rounded-lg border border-ink-100 px-2 py-2 text-sm bg-white">
        <option value="">{{ $batch->course->term_type === 'semester' ? 'Semester…' : 'Year…' }}</option>
        @foreach($terms as $t)
          @php $n = $byTerm->firstWhere('term_id', $t->id)?->total ?? 0; @endphp
          <option value="{{ $t->id }}" @selected($currentTerm && $currentTerm->id === $t->id)>
            {{ $t->name }} — {{ $n > 0 ? $n.' students' : 'none enrolled' }}
          </option>
        @endforeach
      </select>

      <button class="w-full rounded-lg bg-ink-800 text-white py-2 text-sm font-medium">Add subject</button>

      @if($subjects->isEmpty())
        <p class="text-xs text-ink-600">
          No subjects exist yet — add them on the
          <a href="{{ route('subjects.index') }}" class="underline underline-offset-2">Subjects</a> page first.
        </p>
      @endif
    </form>
  </div>

</div>

@endsection
