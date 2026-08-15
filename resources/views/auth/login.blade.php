@extends('layouts.app')
@section('title','Sign in')
@section('content')
<div class="max-w-sm mx-auto mt-10">
  <h1 class="text-xl font-semibold mb-1">Sign in</h1>
  <p class="text-sm text-ink-600 mb-6">RPIIT Campus ERP</p>

  @if($errors->any())
    <div class="mb-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 text-sm">
      {{ $errors->first() }}
    </div>
  @endif

  <form method="POST" action="{{ route('login.attempt') }}" class="bg-white rounded-xl border border-ink-100 p-5 space-y-4">
    @csrf
    <div>
      <label class="block text-sm font-medium mb-1">Login id or email</label>
      <input name="login" value="{{ old('login') }}" required autofocus autocapitalize="none"
             class="w-full rounded-lg border-ink-100 border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-ink-600">
    </div>
    <div>
      <label class="block text-sm font-medium mb-1">Password</label>
      <input type="password" name="password" required
             class="w-full rounded-lg border-ink-100 border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-ink-600">
    </div>
    <button class="w-full rounded-lg bg-ink-800 text-white py-2.5 font-medium hover:bg-ink-900">Sign in</button>
  </form>
</div>
@endsection
