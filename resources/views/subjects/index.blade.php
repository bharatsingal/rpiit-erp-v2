@extends('layouts.app')
@section('title','Subjects')
@section('content')

<h1 class="text-lg font-semibold mb-4">Subjects</h1>

<div class="grid lg:grid-cols-3 gap-4">
  <form method="POST" action="{{ route('subjects.store') }}"
        class="bg-white rounded-xl border border-ink-100 p-4 space-y-3 h-fit">
    @csrf
    <div class="font-medium text-sm">Add a subject</div>
    @error('code')<p class="text-xs text-rose-700">{{ $message }}</p>@enderror
    <input name="code" value="{{ old('code') }}" required placeholder="Code — e.g. BP301T"
           class="w-full rounded-lg border border-ink-100 px-3 py-2 text-sm">
    <input name="name" value="{{ old('name') }}" required placeholder="Subject name"
           class="w-full rounded-lg border border-ink-100 px-3 py-2 text-sm">
    <div class="flex gap-2">
      <input name="credits" type="number" min="0" max="20" value="{{ old('credits', 0) }}" placeholder="Credits"
             class="w-24 rounded-lg border border-ink-100 px-3 py-2 text-sm">
      <select name="type" class="flex-1 rounded-lg border border-ink-100 px-2 py-2 text-sm bg-white">
        <option value="theory">Theory</option>
        <option value="practical">Practical / lab</option>
        <option value="project">Project</option>
      </select>
    </div>
    <button class="w-full rounded-lg bg-ink-800 text-white py-2 text-sm font-medium">Add subject</button>
  </form>

  <div class="lg:col-span-2">
    <form method="GET" class="mb-3">
      <input name="q" value="{{ request('q') }}" placeholder="Search subjects"
             class="w-full rounded-lg border border-ink-100 px-3 py-2 text-sm">
    </form>
    <div class="bg-white rounded-xl border border-ink-100 overflow-hidden">
      <ul class="divide-y divide-ink-100">
        @forelse($subjects as $s)
          <li class="px-4 py-3 flex items-center gap-3 text-sm">
            <span class="font-mono text-xs bg-ink-50 rounded px-2 py-1">{{ $s->code }}</span>
            <span class="flex-1 min-w-0 truncate">{{ $s->name }}</span>
            <span class="text-xs text-ink-600">{{ ucfirst($s->type) }}</span>
            <span class="text-xs text-ink-600">{{ $s->offerings_count }} {{ Str::plural('class', $s->offerings_count) }}</span>
          </li>
        @empty
          <li class="px-4 py-8 text-center text-sm text-ink-600">
            No subjects yet. Add the first one on the left.
          </li>
        @endforelse
      </ul>
    </div>
    <div class="mt-3">{{ $subjects->withQueryString()->links() }}</div>
  </div>
</div>

@endsection
