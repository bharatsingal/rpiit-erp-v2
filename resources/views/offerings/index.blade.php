@extends('layouts.app')
@section('title','Classes')
@section('content')

<h1 class="text-lg font-semibold mb-1">Classes</h1>
<p class="text-sm text-ink-600 mb-4">
  A class is one subject taught to one batch in one term.
  {{ $year?->name ? 'Academic year '.$year->name : 'No current academic year set.' }}
</p>

<div class="grid lg:grid-cols-3 gap-4">
  <form method="POST" action="{{ route('offerings.store') }}" x-data="{ terms: [], loading: false }"
        class="bg-white rounded-xl border border-ink-100 p-4 space-y-3 h-fit">
    @csrf
    <div class="font-medium text-sm">Add a class</div>
    @if($errors->any())<p class="text-xs text-rose-700">{{ $errors->first() }}</p>@endif

    <select name="subject_id" required class="w-full rounded-lg border border-ink-100 px-2 py-2 text-sm bg-white">
      <option value="">Subject…</option>
      @foreach($subjects as $s)<option value="{{ $s->id }}">{{ $s->code }} — {{ $s->name }}</option>@endforeach
    </select>

    <select name="batch_id" required class="w-full rounded-lg border border-ink-100 px-2 py-2 text-sm bg-white"
            @change="loading = true; terms = []; fetch('/offerings/terms/' + $event.target.value)
                      .then(r => r.json()).then(d => { terms = d; loading = false })">
      <option value="">Batch…</option>
      @foreach($batches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
    </select>

    <select name="term_id" required class="w-full rounded-lg border border-ink-100 px-2 py-2 text-sm bg-white">
      <option value="">Term…</option>
      <template x-for="t in terms" :key="t.id">
        <option :value="t.id" x-text="t.name"></option>
      </template>
    </select>
    <p class="text-xs text-ink-600" x-show="loading">Loading terms…</p>

    <select name="faculty_id" class="w-full rounded-lg border border-ink-100 px-2 py-2 text-sm bg-white">
      <option value="">Faculty — optional</option>
      @foreach($teachers as $t)<option value="{{ $t->user_id }}">{{ $t->name }}</option>@endforeach
    </select>

    <button class="w-full rounded-lg bg-ink-800 text-white py-2 text-sm font-medium">Add class</button>
    @if($teachers->isEmpty())
      <p class="text-xs text-ink-600">
        No faculty have sign-in accounts yet, so the list is empty. A class works without one.
      </p>
    @endif
  </form>

  <div class="lg:col-span-2 bg-white rounded-xl border border-ink-100 overflow-hidden">
    <ul class="divide-y divide-ink-100">
      @forelse($offerings as $o)
        <li class="px-4 py-3 text-sm flex items-center gap-3">
          <span class="min-w-0 flex-1">
            <span class="block font-medium truncate">{{ $o->subject?->code }} — {{ $o->subject?->name }}</span>
            <span class="block text-xs text-ink-600">
              {{ $o->batch?->name }} · {{ $o->term?->name }}@if($o->faculty) · {{ $o->faculty->name }}@endif
            </span>
          </span>
          <span class="text-xs text-ink-600 whitespace-nowrap">
            {{ $o->attendance_sessions_count }} {{ Str::plural('session', $o->attendance_sessions_count) }}
          </span>
          <a href="{{ route('attendance.create', $o) }}"
             class="text-xs rounded-lg bg-ink-50 px-3 py-1.5 font-medium hover:bg-ink-100">Mark</a>
        </li>
      @empty
        <li class="px-4 py-8 text-center text-sm text-ink-600">
          No classes yet. Add subjects first, then assign one to a batch here.
        </li>
      @endforelse
    </ul>
  </div>
</div>

@endsection
