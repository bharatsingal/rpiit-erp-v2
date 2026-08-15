<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>RPIIT Technical &amp; Medical Campus — Karnal</title>
<meta name="description" content="RP Inderaprastha Institute of Technology, Karnal. Pharmacy, Nursing, Paramedical, Physiotherapy, Engineering, Management and Hotel Management programmes.">
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
  tailwind.config = { theme: { extend: { colors: {
    ink: { 50:'#f4f6fb',100:'#e8ecf6',200:'#cdd6e9',600:'#3d4d80',700:'#31406c',800:'#28345a',900:'#1e2747' },
    gold:{ 400:'#e0b64a', 500:'#c99b2e' },
  } } } }
</script>
</head>
<body class="bg-white text-ink-900 antialiased">

<!-- contact strip -->
<div class="bg-ink-900 text-white/80 text-xs">
  <div class="mx-auto max-w-6xl px-4 h-9 flex items-center gap-5">
    <a href="mailto:info@rpiit.com" class="hover:text-white">info@rpiit.com</a>
    <a href="tel:+919215678929" class="hover:text-white">+91 92156 78929</a>
    <span class="ml-auto hidden sm:block">NH-44, Bastara, Karnal, Haryana 132114</span>
  </div>
</div>

<!-- header -->
<header x-data="{ open: false }" class="bg-white border-b border-ink-100 sticky top-0 z-40">
  <div class="mx-auto max-w-6xl px-4 h-16 flex items-center gap-4">
    <a href="/" class="flex items-center gap-3">
      <span class="grid place-items-center w-10 h-10 rounded-lg bg-ink-800 text-white font-bold tracking-tight">RP</span>
      <span class="leading-tight">
        <span class="block font-semibold">RPIIT</span>
        <span class="block text-[11px] text-ink-600">Technical &amp; Medical Campus</span>
      </span>
    </a>

    <nav class="ml-auto hidden md:flex items-center gap-6 text-sm">
      <a href="#about"    class="text-ink-600 hover:text-ink-900">About</a>
      <a href="#courses"  class="text-ink-600 hover:text-ink-900">Courses</a>
      <a href="#why"      class="text-ink-600 hover:text-ink-900">Why RPIIT</a>
      <a href="#contact"  class="text-ink-600 hover:text-ink-900">Contact</a>
    </nav>

    @auth
      <a href="{{ route('dashboard') }}"
         class="hidden md:inline-block rounded-full bg-ink-800 text-white px-5 py-2 text-sm font-medium hover:bg-ink-900">
        Go to ERP
      </a>
    @else
      <a href="{{ route('login') }}"
         class="hidden md:inline-block rounded-full bg-ink-800 text-white px-5 py-2 text-sm font-medium hover:bg-ink-900">
        Login ERP
      </a>
    @endauth

    <button @click="open = !open" class="md:hidden ml-auto p-2 -mr-2" aria-label="Menu">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/>
      </svg>
    </button>
  </div>

  <div x-show="open" x-cloak class="md:hidden border-t border-ink-100 px-4 py-3 space-y-2 text-sm">
    <a href="#about" @click="open=false" class="block py-1.5 text-ink-600">About</a>
    <a href="#courses" @click="open=false" class="block py-1.5 text-ink-600">Courses</a>
    <a href="#why" @click="open=false" class="block py-1.5 text-ink-600">Why RPIIT</a>
    <a href="#contact" @click="open=false" class="block py-1.5 text-ink-600">Contact</a>
    <a href="{{ auth()->check() ? route('dashboard') : route('login') }}"
       class="block mt-2 rounded-full bg-ink-800 text-white px-5 py-2.5 text-center font-medium">
      {{ auth()->check() ? 'Go to ERP' : 'Login ERP' }}
    </a>
  </div>
</header>

<!-- hero -->
<section class="relative bg-ink-900 text-white overflow-hidden">
  <div class="absolute inset-0 opacity-20"
       style="background-image:radial-gradient(circle at 20% 30%, #3d4d80 0%, transparent 55%),
                              radial-gradient(circle at 80% 70%, #c99b2e 0%, transparent 45%)"></div>
  <div class="relative mx-auto max-w-6xl px-4 py-20 md:py-28">
    <p class="inline-block rounded-full bg-white/10 px-3 py-1 text-xs tracking-widest uppercase mb-5">
      RP Inderaprastha Institute of Technology
    </p>
    <h1 class="text-3xl md:text-5xl font-semibold leading-tight max-w-3xl">
      Empowering the next generation of professionals
    </h1>
    <p class="mt-5 text-white/70 max-w-xl leading-relaxed">
      A multi-discipline campus at Karnal offering pharmacy, nursing, paramedical,
      physiotherapy, engineering, management and hotel management programmes —
      run by the R.P. Educational Trust.
    </p>
    <div class="mt-8 flex flex-wrap gap-3">
      <a href="#courses" class="rounded-full bg-white text-ink-900 px-6 py-3 text-sm font-medium hover:bg-ink-50">
        Explore courses
      </a>
      <a href="{{ route('login') }}" class="rounded-full border border-white/30 px-6 py-3 text-sm font-medium hover:bg-white/10">
        Student &amp; staff login
      </a>
    </div>

    <dl class="mt-14 grid grid-cols-3 gap-6 max-w-lg">
      @foreach([[number_format($students),'Students'],[$programmes,'Programmes'],[number_format($staff),'Faculty &amp; staff']] as [$v,$l])
        <div>
          <dt class="text-2xl md:text-3xl font-semibold tabular-nums">{{ $v }}</dt>
          <dd class="text-xs text-white/60 uppercase tracking-wide mt-1">{!! $l !!}</dd>
        </div>
      @endforeach
    </dl>
  </div>
</section>

<!-- about -->
<section id="about" class="mx-auto max-w-6xl px-4 py-16">
  <div class="grid md:grid-cols-2 gap-10 items-start">
    <div>
      <h2 class="text-2xl font-semibold mb-3">About the campus</h2>
      <p class="text-ink-600 leading-relaxed mb-4">
        RPIIT Technical &amp; Medical Campus sits on NH-44 at Bastara, Karnal. Across its
        departments it teaches pharmacy, nursing, medical laboratory technology,
        physiotherapy, engineering, management and hotel management — with laboratories,
        clinical placements, hostels and transport supporting students from across Haryana
        and beyond.
      </p>
      <p class="text-ink-600 leading-relaxed">
        Programmes are affiliated to their respective councils and boards, and the campus
        runs both degree and diploma routes, including lateral entry for diploma holders.
      </p>
    </div>
    <div class="bg-ink-50 rounded-2xl p-6">
      <h3 class="font-medium mb-4">Departments</h3>
      <ul class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm text-ink-600">
        @foreach(['Pharmacy','Nursing','Paramedical (DMLT)','Physiotherapy','Computer Science','Civil Engineering','Applied Science','Management','Hotel Management','Library','Transport','Hostel'] as $d)
          <li class="flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-gold-500"></span>{{ $d }}
          </li>
        @endforeach
      </ul>
    </div>
  </div>
</section>

<!-- courses, from the ERP -->
<section id="courses" class="bg-ink-50 py-16">
  <div class="mx-auto max-w-6xl px-4">
    <h2 class="text-2xl font-semibold mb-1">Courses offered</h2>
    <p class="text-sm text-ink-600 mb-8">Lateral-entry routes are available for several programmes.</p>

    @php
      $labels = ['pharmacy'=>'Pharmacy','nursing'=>'Nursing','paramedical'=>'Paramedical',
                 'physiotherapy'=>'Physiotherapy','engineering'=>'Engineering',
                 'management'=>'Management','computer'=>'Computer applications',
                 'hotel'=>'Hotel management','other'=>'Other'];
      $order = ['pharmacy','nursing','paramedical','physiotherapy','engineering','management','computer','hotel','other'];
    @endphp

    @foreach($order as $key)
      @continue(!isset($courses[$key]))
      <div class="mb-8">
        <h3 class="text-xs uppercase tracking-widest text-ink-600 mb-3">{{ $labels[$key] }}</h3>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
          @foreach($courses[$key] as $c)
            <div class="bg-white rounded-xl border border-ink-100 p-4">
              <div class="font-medium">{{ $c->name }}</div>
              <div class="text-xs text-ink-600 mt-1">
                {{ $c->duration_years }} {{ Str::plural('year', $c->duration_years) }} ·
                {{ $c->term_type === 'semester' ? 'Semester system' : 'Annual system' }}
              </div>
            </div>
          @endforeach
        </div>
      </div>
    @endforeach
  </div>
</section>

<!-- why -->
<section id="why" class="mx-auto max-w-6xl px-4 py-16">
  <h2 class="text-2xl font-semibold mb-8">Why RPIIT</h2>
  <div class="grid md:grid-cols-3 gap-5">
    @foreach([
      ['Clinical and lab exposure','Pharmacy, nursing, DMLT and physiotherapy students train in on-campus laboratories and at attached clinical sites.'],
      ['Hostel and transport','Separate boys and girls hostels with wardens and mess, and a bus fleet covering routes across the district.'],
      ['Lateral entry routes','Diploma holders can join B.Pharmacy, DMLT, B.Tech and Diploma programmes directly in the second year.'],
      ['Placement and training','A training coordinator tracks industrial training and internships for every batch.'],
      ['Library and resources','A central library serving all departments, with departmental reference sections.'],
      ['Digital campus','Attendance, fees, results and notices run through the campus ERP, accessible to students and parents.'],
    ] as [$t,$d])
      <div class="rounded-xl border border-ink-100 p-5">
        <div class="w-9 h-9 rounded-lg bg-ink-800 text-white grid place-items-center mb-3">
          <span class="w-2 h-2 rounded-full bg-gold-400"></span>
        </div>
        <div class="font-medium mb-1">{{ $t }}</div>
        <p class="text-sm text-ink-600 leading-relaxed">{{ $d }}</p>
      </div>
    @endforeach
  </div>
</section>

<!-- contact -->
<section id="contact" class="bg-ink-900 text-white py-16">
  <div class="mx-auto max-w-6xl px-4 grid md:grid-cols-3 gap-8">
    <div>
      <h2 class="text-xl font-semibold mb-3">Visit us</h2>
      <p class="text-white/70 text-sm leading-relaxed">
        RPIIT Technical &amp; Medical Campus<br>
        G.T. Road, NH-44, Near Toll Plaza<br>
        Bastara, Karnal<br>
        Haryana 132114
      </p>
    </div>
    <div>
      <h2 class="text-xl font-semibold mb-3">Get in touch</h2>
      <p class="text-white/70 text-sm leading-relaxed">
        <a href="tel:+919215678929" class="hover:text-white">+91 92156 78929</a><br>
        <a href="mailto:info@rpiit.com" class="hover:text-white">info@rpiit.com</a>
      </p>
    </div>
    <div>
      <h2 class="text-xl font-semibold mb-3">Campus ERP</h2>
      <p class="text-white/70 text-sm leading-relaxed mb-4">
        Students, parents and staff sign in for attendance, fees, results and notices.
      </p>
      <a href="{{ auth()->check() ? route('dashboard') : route('login') }}"
         class="inline-block rounded-full bg-white text-ink-900 px-5 py-2.5 text-sm font-medium hover:bg-ink-50">
        {{ auth()->check() ? 'Go to ERP' : 'Login ERP' }}
      </a>
    </div>
  </div>
</section>

<footer class="bg-ink-900 border-t border-white/10 text-white/50 text-xs">
  <div class="mx-auto max-w-6xl px-4 py-6 flex flex-wrap gap-2 justify-between">
    <span>© {{ date('Y') }} R.P. Educational Trust, Karnal</span>
    <span>RPIIT Technical &amp; Medical Campus</span>
  </div>
</footer>

<style>[x-cloak]{display:none!important}</style>
</body>
</html>
