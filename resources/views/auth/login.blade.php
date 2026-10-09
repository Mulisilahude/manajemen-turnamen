@extends('layouts.app')

@section('title', 'Masuk Admin - Manajemen Turnamen')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <div class="rounded-2xl border border-slate-800 bg-gradient-to-br from-slate-950 via-blue-950/60 to-cyan-900/40 p-6 shadow-xl shadow-cyan-900/10">
            <div class="mb-6 text-center">
                <h1 class="text-2xl font-semibold">Masuk Administrator</h1>
                <p class="mt-2 text-sm text-slate-400">Hanya untuk pengelola turnamen</p>
            </div>
            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="mb-2 block text-sm text-slate-300">Alamat Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="w-full rounded-xl border border-slate-800 bg-slate-950/60 px-4 py-2.5 text-sm outline-none ring-cyan-500/40 focus:ring-2">
                    @error('email')
                        <p class="mt-2 text-sm text-rose-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password" class="mb-2 block text-sm text-slate-300">Kata Sandi</label>
                    <input id="password" name="password" type="password" required class="w-full rounded-xl border border-slate-800 bg-slate-950/60 px-4 py-2.5 text-sm outline-none ring-cyan-500/40 focus:ring-2">
                </div>
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm text-slate-400">
                        <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-700 bg-slate-950">
                        Ingat saya
                    </label>
                </div>
                <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-blue-600 to-cyan-500 px-4 py-2.5 text-sm font-medium transition hover:from-blue-500 hover:to-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-400/40">
                    Masuk
                </button>
            </form>
        </div>
    </div>
</div>
@endsection