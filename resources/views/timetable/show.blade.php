@extends('layouts.app')
@section('title','Timetable — '.$batch->name)
@section('content')

<a href="{{ route('batches.show', $batch) }}" class="text-sm text-ink-600">&larr; {{ $batch->name }}</a>

<div class="mt-1 mb-5 flex flex-wrap items-baseline justify-between gap-2">
  <div>
    <h1 class="text-lg font-semibold">Timetable — {{ $batch->name }}</h1>
    <p class="text-sm text-ink-600">
      {{ $year?->name ? 'Academic year '.$year->name : '' }} ·
      {{ $offerings->count() }} {{ Str::plural('subject', $offerings->count()) }} available
    </p>
  </div>
</div>

@if($offerings->isEmpty())
  <div class="bg-white rounded-xl border border-ink-100 p-6 text-sm">
    <p class="font-medium mb-1">No subjects assigned to this batch yet.</p>
    <p class="text-ink-600">
      Add subjects on the <a href="{{ route('batches.show', $batch) }}" class="underline underline-offset-2">batch page</a>
      first — a timetable can only place subjects that already exist.
    </p>
  </div>
@else

<div class="bg-white rounded-xl border border-ink-100 overflow-x-auto">
  <table class="w-full text-sm border-collapse">
    <thead>
      <tr class="bg-ink-50">
        <th class="text-left font-medium px-3 py-2.5 w-28 border-b border-ink-100">Period</th>
        @foreach($days as $n => $day)
          <th class="text-left font-medium px-3 py-2.5 border-b border-l border-ink-100 min-w-40">{{ $day }}</th>
        @endforeach
      </tr>
    </thead>
    <tbody>
      @foreach($periods as $p)
        <tr class="{{ $p->is_teaching ? '' : 'bg-ink-50/60' }}">
          <td class="px-3 py-2 border-b border-ink-100 align-top">
            <div class="font-medium">{{ $p->label ?: 'Period '.$p->number }}</div>
            <div class="text-[11px] text-ink-600 tabular-nums">{{ $p->timeRange() }}</div>
          </td>

          @foreach($days as $dayNo => $day)
            <td class="px-2 py-2 border-b border-l border-ink-100 align-top">
              @if(! $p->is_teaching)
                <span class="text-xs text-ink-600">—</span>
              @else
                @php $slot = $slots[$dayNo.'-'.$p->number] ?? null; @endphp
                <form method="POST" action="{{ route('timetable.store', $batch) }}" class="space-y-1">
                  @csrf
                  <input type="hidden" name="day" value="{{ $dayNo }}">
                  <input type="hidden" name="period" value="{{ $p->number }}">
                  <select name="offering" onchange="this.form.submit()"
                          class="w-full rounded-lg border px-2 py-1.5 text-xs bg-white
                                 {{ $slot ? 'border-ink-600 font-medium' : 'border-ink-100 text-ink-600' }}">
                    <option value="">— free —</option>
                    @foreach($offerings as $o)
                      <option value="{{ $o->id }}" @selected($slot && $slot->subject_offering_id === $o->id)>
                        {{ $o->subject?->code }}
                      </option>
                    @endforeach
                  </select>
                  @if($slot)
                    <div class="text-[11px] text-ink-600 leading-tight px-0.5">
                      {{ Str::limit($slot->subjectOffering?->subject?->name, 28) }}
                      @if($slot->subjectOffering?->faculty)
                        <br>{{ $slot->subjectOffering->faculty->name }}
                      @endif
                    </div>
                  @endif
                </form>
              @endif
            </td>
          @endforeach
        </tr>
      @endforeach
    </tbody>
  </table>
</div>

<p class="text-xs text-ink-600 mt-3">
  Pick a subject to fill a slot; choose <em>free</em> to clear it. Times come from the campus bell
  schedule, so changing a period time updates every timetable at once.
</p>

@endif

@endsection
