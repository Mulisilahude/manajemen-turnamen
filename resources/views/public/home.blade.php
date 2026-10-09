@extends('layouts.app')

@section('title', 'Beranda - Manajemen Turnamen')

@section('content')
<div class="relative overflow-hidden">
    <div class="absolute inset-0 -z-10 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-cyan-500/20 via-blue-900/20 to-slate-950"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-20">
        <div class="max-w-3xl">
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-semibold tracking-tight leading-tight">
                Platform Manajemen Turnamen Olahraga
            </h1>
            <p class="mt-4 text-base sm:text-lg text-slate-300">
                Solusi terpusat untuk mengelola turnamen, tim, pemain, wasit, jadwal pertandingan, dan statistik. Dibangun untuk digunakan lintas cabang olahraga tanpa mengunci nama event tertentu.
            </p>
            <div class="mt-8 flex flex-wrap items-center gap-3">
                <a href="{{ route('public.tournaments.index') }}" class="rounded-xl bg-gradient-to-r from-blue-600 to-cyan-500 px-5 py-2.5 text-sm font-medium transition hover:from-blue-500 hover:to-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-400/40">
                    Lihat Daftar Turnamen
                </a>
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="rounded-xl border border-slate-700 px-5 py-2.5 text-sm font-medium transition hover:border-cyan-400/60 hover:text-cyan-300">
                            Buka Dashboard Admin
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="rounded-xl border border-slate-700 px-5 py-2.5 text-sm font-medium transition hover:border-cyan-400/60 hover:text-cyan-300">
                        Masuk sebagai Admin
                    </a>
                @endauth
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <h2 class="text-xl sm:text-2xl font-semibold">Turnamen Terbaru</h2>
        <a href="{{ route('public.tournaments.index') }}" class="text-sm text-cyan-300 hover:text-cyan-200 transition">Lihat semua</a>
    </div>
    @if($tournaments->isEmpty())
        <div class="rounded-2xl border border-slate-800 bg-slate-900/40 p-8 text-center text-slate-400">
            Belum ada turnamen yang dipublikasikan. Silakan cek kembali nanti.
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($tournaments as $tournament)
                <article class="rounded-2xl border border-slate-800 bg-gradient-to-br from-slate-950 via-blue-950/40 to-cyan-900/30 p-5 shadow-lg shadow-cyan-900/5 transition hover:border-cyan-400/60 hover:-translate-y-0.5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold">{{ $tournament->nama }}</h3>
                            <p class="mt-1 text-sm text-slate-400">{{ $tournament->cabang_olahraga ?? 'Cabang olahraga belum ditentukan' }}</p>
                        </div>
                        <span class="rounded-full border border-cyan-400/40 bg-cyan-500/10 px-2.5 py-1 text-xs text-cyan-200">{{ $tournament->status?->value ?? $tournament->status }}</span>
                    </div>
                    <div class="mt-4 space-y-2 text-sm text-slate-300">
                        <p class="flex items-center gap-2">
                            <span class="text-slate-400">Tanggal</span>
                            <span>{{ optional($tournament->tanggal_mulai)->format('d M Y') }} - {{ optional($tournament->tanggal_selesai)->format('d M Y') }}</span>
                        </p>
                        @if($tournament->lokasi)
                            <p class="flex items-center gap-2">
                                <span class="text-slate-400">Lokasi</span>
                                <span>{{ $tournament->lokasi }}</span>
                            </p>
                        @endif
                    </div>
                    <div class="mt-6">
                        <a href="{{ route('public.tournaments.show', $tournament) }}" class="inline-flex items-center gap-2 text-sm text-cyan-300 hover:text-cyan-200 transition">
                            Lihat detail turnamen
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
@endsection