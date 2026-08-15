<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RPIIT Campus')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
      tailwind.config = { theme: { extend: { colors: {
        ink: { 50:'#f4f6fb',100:'#e8ecf6',200:'#cdd6e9',500:'#5b6b9e',600:'#3d4d80',700:'#31406c',800:'#28345a',900:'#1e2747' },
      } } } }
    </script>
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="h-full bg-ink-50 text-ink-900 antialiased">

@php
  // Top nav: the sections a member of staff moves between.
  $sections = [
    ['dashboard',       'Dashboard',  'M3 12l9-9 9 9M5 10v10h14V10'],
    ['courses.index',   'Academic',   'M12 3L2 8l10 5 10-5-10-5zM4 12v5c0 1 4 3 8 3s8-2 8-3v-5'],
    ['students.index',  'Students',   'M12 12a4 4 0 100-8 4 4 0 000 8zM4 21v-1a6 6 0 0116 0v1'],
    ['staff.index',     'Staff',      'M9 11a3 3 0 100-6 3 3 0 000 6zm8 0a3 3 0 100-6 3 3 0 000 6zM2 20v-1a5 5 0 0110 0v1m0-1a5 5 0 0110 0v1'],
    ['attendance.index','Attendance', 'M9 11l3 3 5-6M4 5h16v16H4z'],
    ['subjects.index',  'Subjects',   'M4 5h16v14H4zM8 5v14M4 9h4'],
  ];
  $user = auth()->user();
@endphp

@auth
<header class="bg-ink-900 text-white">
  <div class="mx-auto max-w-7xl px-4 h-16 flex items-center gap-4">
    <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 shrink-0">
      <span class="grid place-items-center w-10 h-10 rounded-lg bg-white text-ink-900 font-bold text-sm tracking-tight">RP</span>
      <span class="leading-tight hidden sm:block">
        <span class="block font-semibold text-sm">RPIIT</span>
        <span class="block text-[10px] text-white/50">Technical &amp; Medical Campus</span>
      </span>
    </a>

    <a href="{{ route('home') }}" target="_blank"
       class="ml-2 hidden sm:flex items-center gap-1.5 rounded-lg border border-white/25 px-3 py-1.5 text-xs hover:bg-white/10">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 010 18a15 15 0 010-18"/>
      </svg>
      View website
    </a>

    <div class="ml-auto flex items-center gap-3 text-sm">
      <span class="hidden sm:flex items-center gap-2">
        <span class="text-white/80">{{ $user->staff?->name ?? $user->name }}</span>
        @if(! $user->seesEverything() && $user->department())
          <span class="text-[10px] uppercase tracking-wide bg-white/15 rounded px-1.5 py-0.5">
            {{ $user->department()->name }}
          </span>
        @endif
      </span>
      <form method="POST" action="{{ route('logout') }}">@csrf
        <button class="text-white/70 hover:text-white text-xs">Sign out</button>
      </form>
    </div>
  </div>

  <!-- icon section nav -->
  <nav class="bg-ink-800">
    <div class="mx-auto max-w-7xl px-4 flex overflow-x-auto">
      @foreach($sections as [$route, $label, $path])
        @php $on = request()->routeIs(str_replace('.index','.*',$route)) || request()->routeIs($route); @endphp
        <a href="{{ route($route) }}"
           class="shrink-0 px-5 sm:px-7 py-2.5 flex flex-col items-center gap-1 border-b-2 transition
                  {{ $on ? 'bg-white text-ink-900 border-white' : 'text-white/75 border-transparent hover:text-white hover:bg-white/5' }}">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/>
          </svg>
          <span class="text-[10px] font-semibold uppercase tracking-wide whitespace-nowrap">{{ $label }}</span>
        </a>
      @endforeach
    </div>
  </nav>
</header>
@endauth

<div class="mx-auto max-w-7xl px-4 py-5 flex gap-5">

  @auth
  <!-- sidebar -->
  <aside class="hidden lg:block w-56 shrink-0">
    <nav class="bg-white rounded-xl border border-ink-100 overflow-hidden text-sm sticky top-5">
      @php $side = [
        ['dashboard',        'Dashboard'],
        ['courses.index',    'Courses &amp; batches'],
        ['students.index',   'Students'],
        ['staff.index',      'Staff directory'],
        ['subjects.index',   'Subjects'],
        ['attendance.index', 'Mark attendance'],
      ]; @endphp
      @foreach($side as [$route, $label])
        @php $on = request()->routeIs($route); @endphp
        <a href="{{ route($route) }}"
           class="block px-4 py-2.5 border-l-2 {{ $on ? 'border-ink-800 bg-ink-50 font-medium' : 'border-transparent text-ink-600 hover:bg-ink-50' }}">
          {!! $label !!}
        </a>
      @endforeach
    </nav>
  </aside>
  @endauth

  <main class="flex-1 min-w-0">
    @if(session('status'))
      <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">
        {{ session('status') }}
      </div>
    @endif
    @yield('content')
  </main>
</div>

</body>
</html>
