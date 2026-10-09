@extends('layouts.app')

@section('title', $tournament->nama . ' - Manajemen Turnamen')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <a href="{{ route('public.tournaments.index') }}" class="text-sm text-cyan-300 hover:text-cyan-200 transition">← Kembali ke daftar turnamen</a>
        <h1 class="mt-4 text-2xl sm:text-3xl font-semibold">{{ $tournament->nama }}</h1>
        <p class="mt-2 text-sm text-slate-400">Cabang olahraga: {{ $tournament->cabang_olahraga ?? 'Belum ditentukan' }} • Status: {{ $tournament->status?->value ?? $tournament->status }}</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-[2fr_1fr]">
        <div class="rounded-2xl border border-slate-800 bg-gradient-to-br from-slate-950 via-blue-950/40 to-cyan-900/30 p-6">
            <h2 class="text-lg font-semibold">Deskripsi</h2>
            @if($tournament->deskripsi)
                <div class="mt-3 prose prose-invert prose-sm max-w-none text-slate-300">
                    {{ $tournament->deskripsi }}
                </div>
            @else
                <p class="mt-3 text-sm text-slate-400">Belum ada deskripsi turnamen.</p>
            @endif
        </div>
        <aside class="space-y-6">
            <div class="rounded-2xl border border-slate-800 bg-slate-900/40 p-6">
                <h2 class="text-lg font-semibold">Informasi Turnamen</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-slate-400">Tanggal Mulai</dt>
                        <dd class="text-slate-200">{{ optional($tournament->tanggal_mulai)->format('d M Y') }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-slate-400">Tanggal Selesai</dt>
                        <dd class="text-slate-200">{{ optional($tournament->tanggal_selesai)->format('d M Y') }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-slate-400">Lokasi</dt>
                        <dd class="text-slate-200">{{ $tournament->lokasi ?? '-' }}</dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-slate-400">Cabang Olahraga</dt>
                        <dd class="text-slate-200">{{ $tournament->cabang_olahraga ?? '-' }}</dd>
                    </div>
                </dl>
            </div>
            <div class="rounded-2xl border border-slate-800 bg-slate-900/40 p-6">
                <h2 class="text-lg font-semibold">Status</h2>
                <p class="mt-2 text-sm text-slate-300">{{ $tournament->status?->value ?? $tournament->status }}</p>
            </div>
        </aside>
    </div>
</div>
@endsection