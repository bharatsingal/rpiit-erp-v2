@extends('layouts.app')
@section('title','Mark attendance')
@section('content')

<div x-data="attendance()" class="pb-28">

  <div class="mb-4">
    <a href="{{ route('attendance.index') }}" class="text-sm text-ink-600">&larr; All classes</a>
    <h1 class="text-lg font-semibold mt-1">{{ $offering->subject?->name }}</h1>
    <p class="text-sm text-ink-600">
      {{ $offering->batch?->name }}@if($offering->section) · Section {{ $offering->section->name }}@endif
      · {{ \Carbon\Carbon::parse($date)->format('D j M Y') }}
    </p>
    @if($session)
      <p class="mt-2 text-xs inline-block rounded-full bg-amber-50 border border-amber-200 text-amber-800 px-2.5 py-1">
        Already marked{{ $session->markedBy ? ' by '.$session->markedBy->name : '' }}
        @if($session->marked_at) at {{ $session->marked_at->format('H:i') }}@endif — saving will replace it
      </p>
    @endif
  </div>

  <form method="POST" action="{{ route('attendance.store', $offering) }}" id="attForm">
    @csrf
    <input type="hidden" name="date" value="{{ $date }}">

    <div class="flex items-center gap-2 mb-3">
      <label class="text-sm text-ink-600">Period</label>
      <select name="period" class="rounded-lg border border-ink-100 px-2 py-1.5 text-sm bg-white">
        <option value="">—</option>
        @for($p = 1; $p <= 8; $p++)
          <option value="{{ $p }}" @selected($session?->period_number == $p)>{{ $p }}</option>
        @endfor
      </select>
      <button type="button" @click="allPresent()"
              class="ml-auto text-sm text-ink-600 underline underline-offset-2">Reset all present</button>
    </div>

    @if($students->isEmpty())
      <div class="bg-white rounded-xl border border-ink-100 p-6 text-sm">
        <p class="font-medium text-ink-900 mb-1">No students in this term.</p>
        @if($elsewhere->isNotEmpty())
          <p class="text-ink-600 mb-3">
            {{ $offering->batch?->name }} students are enrolled in:
          </p>
          <ul class="space-y-1">
            @foreach($elsewhere as $row)
              <li class="text-ink-900">
                <span class="font-medium">{{ $row->term->name }}</span>
                <span class="text-ink-600">— {{ $row->total }} students</span>
              </li>
            @endforeach
          </ul>
          <p class="text-ink-600 mt-3">
            Add the class against that term on the
            <a href="{{ route('offerings.index') }}" class="underline underline-offset-2">Classes</a> page.
          </p>
        @else
          <p class="text-ink-600">
            Nobody from {{ $offering->batch?->name }} is enrolled in the current academic year.
          </p>
        @endif
      </div>
    @else
      <ul class="bg-white rounded-xl border border-ink-100 divide-y divide-ink-100 overflow-hidden">
        @foreach($students as $s)
          @php $st = $existing[$s->id] ?? 'present'; @endphp
          <li>
            <button type="button"
                    @click="toggle({{ $s->id }})"
                    :class="state[{{ $s->id }}] === 'absent' ? 'bg-rose-50' : (state[{{ $s->id }}] === 'late' ? 'bg-amber-50' : '')"
                    class="w-full text-left px-4 py-3.5 flex items-center gap-3 active:bg-ink-50">
              <span class="w-9 h-9 shrink-0 grid place-items-center rounded-full text-xs font-semibold"
                    :class="state[{{ $s->id }}] === 'absent' ? 'bg-rose-600 text-white'
                          : (state[{{ $s->id }}] === 'late' ? 'bg-amber-500 text-white' : 'bg-emerald-600 text-white')"
                    x-text="state[{{ $s->id }}] === 'absent' ? 'A' : (state[{{ $s->id }}] === 'late' ? 'L' : 'P')"></span>
              <span class="min-w-0">
                <span class="block font-medium truncate">{{ $s->first_name }} {{ $s->last_name }}</span>
                <span class="block text-xs text-ink-600">{{ $s->admission_no }}@if($s->roll_no) · {{ $s->roll_no }}@endif</span>
              </span>
            </button>
            <template x-if="state[{{ $s->id }}] === 'absent'">
              <input type="hidden" name="absent[]" value="{{ $s->id }}">
            </template>
            <template x-if="state[{{ $s->id }}] === 'late'">
              <input type="hidden" name="late[]" value="{{ $s->id }}">
            </template>
          </li>
        @endforeach
      </ul>
    @endif

    <div class="fixed bottom-0 inset-x-0 bg-white border-t border-ink-100"
         style="padding-bottom: env(safe-area-inset-bottom)">
      <div class="mx-auto max-w-6xl px-4 py-3 flex items-center gap-3">
        <div class="text-sm">
          <span class="font-semibold text-emerald-700" x-text="presentCount()"></span> present
          ·
          <span class="font-semibold text-rose-700" x-text="absentCount()"></span> absent
          <template x-if="lateCount() > 0">
            <span> · <span class="font-semibold text-amber-600" x-text="lateCount()"></span> late</span>
          </template>
        </div>
        <button class="ml-auto rounded-lg bg-ink-800 text-white px-6 py-2.5 font-medium hover:bg-ink-900"
                @if($students->isEmpty()) disabled @endif>
          Save
        </button>
      </div>
    </div>
  </form>
</div>

<script>
function attendance() {
  return {
    // Everyone starts present. A full-attendance class is a single tap on Save.
    state: {{ Illuminate\Support\Js::from(
        $students->mapWithKeys(fn($s) => [$s->id => ($existing[$s->id] ?? 'present')])
    ) }},
    // present -> absent -> late -> present
    toggle(id) {
      const next = { present: 'absent', absent: 'late', late: 'present' };
      this.state[id] = next[this.state[id]] ?? 'absent';
    },
    allPresent() { for (const k in this.state) this.state[k] = 'present'; },
    presentCount() { return Object.values(this.state).filter(v => v === 'present').length; },
    absentCount()  { return Object.values(this.state).filter(v => v === 'absent').length; },
    lateCount()    { return Object.values(this.state).filter(v => v === 'late').length; },
  }
}
</script>
@endsection
