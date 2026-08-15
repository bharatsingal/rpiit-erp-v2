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
        ink:   { 50:'#f4f6fb',100:'#e8ecf6',600:'#3d4d80',700:'#31406c',800:'#28345a',900:'#1e2747' },
      } } } }
    </script>
</head>
<body class="h-full bg-ink-50 text-ink-900 antialiased">

<header class="bg-ink-800 text-white sticky top-0 z-40">
  <div class="mx-auto max-w-6xl px-4 h-14 flex items-center justify-between">
    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-semibold tracking-tight">
      <span class="grid place-items-center w-7 h-7 rounded bg-white/15 text-xs">RP</span>
      <span>RPIIT Campus</span>
    </a>
    @auth
    <div class="flex items-center gap-4 text-sm">
      <span class="hidden sm:block text-white/70">{{ auth()->user()->name }}</span>
      <form method="POST" action="{{ route('logout') }}">@csrf
        <button class="text-white/80 hover:text-white">Sign out</button>
      </form>
    </div>
    @endauth
  </div>
</header>

@auth
<nav class="bg-white border-b border-ink-100">
  <div class="mx-auto max-w-6xl px-4 flex gap-1 overflow-x-auto text-sm">
    @php $nav = [
      ['dashboard','Dashboard'],
      ['attendance.index','Attendance'],
      ['students.index','Students'],
      ['subjects.index','Subjects'],
      ['offerings.index','Classes'],
    ]; @endphp
    @foreach($nav as [$route,$label])
      @php $on = request()->routeIs(str_replace('.index','.*',$route)) || request()->routeIs($route); @endphp
      <a href="{{ route($route) }}"
         class="px-3 py-3 border-b-2 whitespace-nowrap {{ $on ? 'border-ink-700 text-ink-800 font-medium' : 'border-transparent text-ink-600 hover:text-ink-800' }}">
        {{ $label }}
      </a>
    @endforeach
  </div>
</nav>
@endauth

<main class="mx-auto max-w-6xl px-4 py-6">
  @if(session('status'))
    <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">
      {{ session('status') }}
    </div>
  @endif
  @yield('content')
</main>

</body>
</html>
