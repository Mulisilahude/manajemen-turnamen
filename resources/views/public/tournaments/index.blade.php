@extends('layouts.app')

@section('title', 'Daftar Turnamen - Manajemen Turnamen')

@section('content')
    <section class="hero-bg border-b border-line">
        <div class="app-container py-10 sm:py-12">
            <span class="chip">Katalog Turnamen</span>
            <h1 class="mt-3 text-2xl font-semibold tracking-tight text-ink sm:text-3xl">Daftar Turnamen</h1>
            <p class="mt-2 max-w-2xl text-sm text-muted">
                Jelajahi turnamen yang telah dipublikasikan. Setiap turnamen memiliki halaman jadwal, grup,
                klasemen, dan statistik tersendiri.
            </p>
        </div>
    </section>

    <section class="app-container py-10">
        @if($tournaments->isEmpty())
            <div class="card mx-auto max-w-2xl p-10 text-center">
                <span class="mx-auto inline-flex h-14 w-14 items-center justify-center rounded-2xl surface-muted text-muted">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                </span>
                <h2 class="mt-4 text-lg font-semibold text-ink">Belum ada turnamen</h2>
                <p class="mx-auto mt-2 max-w-md text-sm text-muted">
                    Daftar masih kosong. Turnamen akan tampil di sini setelah dipublikasikan oleh pengelola.
                </p>
            </div>
        @else
            <p class="mb-5 text-sm text-muted">{{ $tournaments->total() }} turnamen ditemukan</p>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($tournaments as $tournament)
                    <article class="card card-interactive flex flex-col p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h2 class="truncate text-lg font-semibold text-ink">{{ $tournament->nama }}</h2>
                                <p class="mt-1 truncate text-sm text-muted">{{ $tournament->cabang_olahraga ?? 'Cabang olahraga belum ditentukan' }}</p>
                            </div>
                            <span class="badge badge-primary shrink-0">{{ $tournament->status?->label() ?? $tournament->status }}</span>
                        </div>

                        <div class="mt-4 space-y-2 text-sm">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-muted">Tanggal</span>
                                <span class="text-ink">{{ optional($tournament->tanggal_mulai)->translatedFormat('d M Y') ?? '-' }} &ndash; {{ optional($tournament->tanggal_selesai)->translatedFormat('d M Y') ?? '-' }}</span>
                            </div>
                            @if($tournament->lokasi)
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-muted">Lokasi</span>
                                    <span class="truncate text-ink">{{ $tournament->lokasi }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="mt-6 flex items-center justify-between gap-3">
                            <a href="{{ route('public.tournaments.show', $tournament) }}" class="text-sm font-medium text-primary hover:opacity-80">Lihat detail &rarr;</a>
                            <a href="{{ route('public.tournaments.schedule', $tournament) }}" class="text-sm text-muted transition hover:text-primary">Jadwal</a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $tournaments->links() }}
            </div>
        @endif
    </section>
@endsection
