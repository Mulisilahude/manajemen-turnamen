@extends('layouts.app')

@section('title', $tournament->nama)

@php
    $jam = fn ($m) => $m->jam ? substr($m->jam, 0, 5) : null;
    $jumlahTim = $tournament->teams()->count();
    $jumlahLaga = $tournament->matches()->count();
@endphp

@section('content')
    <section class="hero-bg relative overflow-hidden border-b border-line">
        <div class="pitch-pattern pointer-events-none absolute inset-0 opacity-60"></div>
        <div class="app-container relative py-10 sm:py-12">
            <div class="flex flex-wrap items-center gap-2">
                <span class="chip">{{ $tournament->cabang_olahraga ?? 'Turnamen' }}</span>
                <span class="badge {{ $tournament->status === \App\TournamentStatus::ACTIVE ? 'badge-success' : 'badge-primary' }}">
                    {{ $tournament->status?->label() ?? $tournament->status }}
                </span>
                @if($tournament->tampil_publik)
                    <span class="badge badge-muted">Dipublikasikan</span>
                @else
                    <span class="badge badge-muted">Belum dipublikasikan</span>
                @endif
            </div>
            <h1 class="mt-4 text-2xl font-semibold tracking-tight text-ink sm:text-3xl">{{ $tournament->nama }}</h1>

            <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2 text-sm text-muted">
                @if($tournament->tanggal_mulai)
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75" /></svg>
                        {{ $tournament->tanggal_mulai->translatedFormat('d M Y') }}@if($tournament->tanggal_selesai) &ndash; {{ $tournament->tanggal_selesai->translatedFormat('d M Y') }}@endif
                    </span>
                @endif
                @if($tournament->lokasi)
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                        {{ $tournament->lokasi }}
                    </span>
                @endif
                <span class="inline-flex items-center gap-1.5">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75" /></svg>
                    {{ $jumlahTim }} tim &middot; {{ $jumlahLaga }} pertandingan
                </span>
            </div>

            @if($tournament->deskripsi)
                <p class="mt-4 max-w-3xl text-sm text-muted">{{ $tournament->deskripsi }}</p>
            @endif

            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('public.tournaments.schedule', $tournament) }}" class="btn btn-primary">Jadwal Pertandingan</a>
                <a href="{{ route('public.tournaments.standings', $tournament) }}" class="btn btn-outline">Klasemen</a>
                <a href="{{ route('public.tournaments.statistics', $tournament) }}" class="btn btn-outline">Statistik</a>
                <a href="{{ route('public.tournaments.bracket', $tournament) }}" class="btn btn-outline">Bagan</a>
            </div>
        </div>
        <div class="grad-bar"></div>
    </section>

    @include('public.tournaments.partials.nav', ['tournament' => $tournament])

    <div class="app-container py-8">
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <div class="card p-6">
                    <h2 class="text-base font-semibold text-ink">Tentang Turnamen</h2>
                    <p class="mt-3 text-sm text-muted">{{ $tournament->deskripsi ?: 'Belum ada deskripsi untuk turnamen ini.' }}</p>
                </div>

                <div id="peserta" class="card p-6">
                    <h2 class="text-base font-semibold text-ink">Peserta &amp; Pembagian Grup</h2>
                    @php $adaGrup = $tournament->rounds->contains(fn ($r) => $r->groups->isNotEmpty()); @endphp
                    @if($adaGrup)
                        <div class="mt-4 space-y-6">
                            @foreach($tournament->rounds as $round)
                                @if($round->groups->isNotEmpty())
                                    <div>
                                        <h3 class="text-sm font-semibold text-ink">{{ $round->nama }}</h3>
                                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                            @foreach($round->groups as $group)
                                                <div class="rounded-xl border border-line surface-muted p-4">
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-xs font-semibold uppercase tracking-wide text-primary">{{ $group->nama }}</span>
                                                        <span class="text-xs text-muted">{{ $group->teams->count() }} tim</span>
                                                    </div>
                                                    <ul class="mt-2 space-y-1 text-sm text-ink">
                                                        @foreach($group->teams as $team)
                                                            <li class="flex items-center gap-2">
                                                                <span class="h-1.5 w-1.5 rounded-full" style="background-image: var(--grad);"></span>
                                                                {{ $team->nama }}
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <p class="mt-3 text-sm text-muted">Belum ada data peserta atau pembagian grup.</p>
                    @endif
                </div>
            </div>

            <div class="space-y-6">
                <div class="card p-6">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-base font-semibold text-ink">Pertandingan Terdekat</h2>
                        <a href="{{ route('public.tournaments.schedule', $tournament) }}" class="text-xs font-medium text-primary hover:opacity-80">Semua</a>
                    </div>
                    <div class="mt-4 space-y-3">
                        @forelse($pertandinganTerdekat as $match)
                            <a href="{{ route('public.tournaments.match', [$tournament, $match]) }}" class="match-card block p-3.5">
                                <div class="text-xs text-muted">
                                    {{ $match->babakLabel() }}@if($match->group) &middot; {{ $match->group->nama }}@endif
                                </div>
                                <div class="mt-1.5 flex items-center gap-2 text-sm">
                                    <span class="flex-1 truncate font-medium text-ink">{{ $match->teamA?->nama ?? 'TBD' }}</span>
                                    <span class="text-xs text-muted">vs</span>
                                    <span class="flex-1 truncate text-right font-medium text-ink">{{ $match->teamB?->nama ?? 'TBD' }}</span>
                                </div>
                                @if($match->tanggal)
                                    <div class="mt-1.5 text-xs text-muted">
                                        {{ $match->tanggal->translatedFormat('d M Y') }}@if($jam($match)) &middot; {{ $jam($match) }} WITA @endif
                                    </div>
                                @endif
                            </a>
                        @empty
                            <p class="rounded-xl border border-dashed border-line p-4 text-center text-sm text-muted">Belum ada jadwal pertandingan.</p>
                        @endforelse
                    </div>
                </div>

                <div class="card p-6">
                    <h2 class="text-base font-semibold text-ink">Informasi</h2>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-2">
                            <dt class="text-muted">Jumlah Tim</dt>
                            <dd class="font-medium text-ink">{{ $jumlahTim }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <dt class="text-muted">Jumlah Pertandingan</dt>
                            <dd class="font-medium text-ink">{{ $jumlahLaga }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <dt class="text-muted">Lokasi</dt>
                            <dd class="font-medium text-ink">{{ $tournament->lokasi ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection
