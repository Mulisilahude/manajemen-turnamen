@extends('layouts.app')

@section('title', 'Daftar Turnamen - Manajemen Turnamen')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-semibold">Daftar Turnamen</h1>
        <p class="mt-2 text-sm text-slate-400">Semua turnamen yang tersedia ditampilkan di sini</p>
    </div>

    @if($tournaments->isEmpty())
        <div class="rounded-2xl border border-slate-800 bg-slate-900/40 p-10 text-center text-slate-400">
            Belum ada turnamen yang terdaftar. Tambahkan turnamen melalui dashboard admin untuk menampilkannya.
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($tournaments as $tournament)
                <article class="rounded-2xl border border-slate-800 bg-gradient-to-br from-slate-950 via-blue-950/40 to-cyan-900/30 p-5 shadow-lg shadow-cyan-900/5 transition hover:border-cyan-400/60 hover:-translate-y-0.5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold">{{ $tournament->nama }}</h2>
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
                            Lihat detail
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="mt-8">
            {{ $tournaments->links() }}
        </div>
    @endif
</div>
@endsection