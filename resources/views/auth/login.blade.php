@extends('layouts.app')

@section('title', 'Masuk Admin - Manajemen Turnamen')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4 py-12 bg-gradient-to-b from-blue-50/60 via-white to-white">
    <div class="w-full max-w-md">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/80">
            <div class="mb-6 text-center">
                <h1 class="text-2xl font-semibold text-slate-900">Masuk Administrator</h1>
                <p class="mt-2 text-sm text-slate-600">Hanya untuk pengelola turnamen</p>
            </div>
            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="mb-2 block text-sm text-slate-700">Alamat Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none ring-blue-500/20 focus:ring-2 focus:border-blue-500">
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password" class="mb-2 block text-sm text-slate-700">Kata Sandi</label>
                    <input id="password" name="password" type="password" required class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none ring-blue-500/20 focus:ring-2 focus:border-blue-500">
                </div>
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 bg-white text-blue-600 focus:ring-blue-500">
                        Ingat saya
                    </label>
                </div>
                <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-blue-600 via-cyan-500 to-cyan-400 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:from-blue-500 hover:via-cyan-500 hover:to-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-400/30">
                    Masuk
                </button>
            </form>
        </div>
    </div>
</div>
@endsection