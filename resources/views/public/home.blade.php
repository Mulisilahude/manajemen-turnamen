@extends('layouts.app')

@section('title', 'Beranda - Manajemen Turnamen')

@php
    use App\TournamentStatus;

    $jam = fn ($m) => $m->jam ? substr($m->jam, 0, 5) : null;
    $tujuan = $sorotan ?? null;
    $adaTurnamen = $tournaments->isNotEmpty();

    $pintasan = $tujuan ? [
        ['label' => 'Jadwal', 'desc' => 'Waktu & lokasi laga', 'route' => 'public.tournaments.schedule', 'ikon' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5'],
        ['label' => 'Tim', 'desc' => 'Peserta turnamen', 'route' => 'public.tournaments.teams', 'ikon' => 'M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z'],
        ['label' => 'Klasemen', 'desc' => 'Peringkat grup', 'route' => 'public.tournaments.standings', 'ikon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z'],
        ['label' => 'Hasil', 'desc' => 'Skor pertandingan', 'route' => 'public.tournaments.results', 'ikon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        ['label' => 'Statistik', 'desc' => 'Top skor & kartu', 'route' => 'public.tournaments.statistics', 'ikon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625Z'],
        ['label' => 'Bagan', 'desc' => 'Fase gugur', 'route' => 'public.tournaments.bracket', 'ikon' => 'M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872'],
    ] : [];
@endphp

@section('content')
    {{-- ============================ HERO ============================ --}}
    <section class="hero-bg relative overflow-hidden border-b border-line">
        <div class="pitch-pattern pointer-events-none absolute inset-0 opacity-70"></div>
        <div class="app-container relative grid items-center gap-12 py-16 lg:grid-cols-[1.1fr_0.9fr] lg:gap-16 lg:py-24">
            <div>
                <span class="chip">Portal Turnamen Olahraga</span>
                <h1 class="mt-5 font-display text-4xl font-bold leading-[1.08] tracking-tight text-ink sm:text-5xl">
                    Pantau turnamen olahraga dalam satu portal.
                </h1>
                <p class="mt-5 max-w-xl text-base leading-relaxed text-muted sm:text-lg">
                    Jadwal pertandingan, klasemen, statistik, dan bagan gugur tersaji rapi dan selalu diperbarui.
                    Pengelola mengatur lewat dashboard, pengunjung mengikuti laga tanpa perlu akun.
                </p>
                <div class="mt-8 flex flex-wrap items-center gap-3">
                    <a href="{{ route('public.tournaments.index') }}" class="btn btn-primary px-6 py-3 text-base">Jelajahi Turnamen</a>
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline px-6 py-3 text-base">Buka Dashboard Admin</a>
                        @endif
                    @endauth
                </div>

                @if($jumlahTurnamen > 0)
                    <dl class="mt-10 grid max-w-xl grid-cols-3 gap-6 border-t border-line pt-7">
                        <div>
                            <dt class="text-[13px] font-semibold uppercase tracking-wide text-muted">Turnamen</dt>
                            <dd class="stat-value mt-1.5 font-display text-3xl text-ink">{{ $jumlahTurnamen }}</dd>
                        </div>
                        <div>
                            <dt class="text-[13px] font-semibold uppercase tracking-wide text-muted">Tim</dt>
                            <dd class="stat-value mt-1.5 font-display text-3xl text-ink">{{ $jumlahTim }}</dd>
                        </div>
                        <div>
                            <dt class="text-[13px] font-semibold uppercase tracking-wide text-muted">Pertandingan</dt>
                            <dd class="stat-value mt-1.5 font-display text-3xl text-ink">{{ $jumlahPertandingan }}</dd>
                        </div>
                    </dl>
                @endif
            </div>

            {{-- Sisi kanan: sorotan nyata bila ada turnamen terbit, jika tidak visual dekoratif. --}}
            <div class="relative mx-auto w-full max-w-md lg:mx-0">
                <div class="pitch-pattern absolute -inset-4 rounded-3xl"></div>
                <div class="card relative overflow-hidden">
                    <div class="grad-bar"></div>
                    @if($sorotan)
                        <div class="flex items-start justify-between gap-3 border-b border-line p-6">
                            <div class="min-w-0">
                                <p class="text-[13px] font-semibold uppercase tracking-wide text-muted">{{ $sorotan->cabang_olahraga ?? 'Turnamen' }}</p>
                                <h3 class="mt-1.5 font-display text-lg font-bold leading-tight text-ink">{{ $sorotan->nama }}</h3>
                            </div>
                            <span class="badge {{ $sorotan->status === \App\TournamentStatus::ACTIVE ? 'badge-success' : 'badge-primary' }}">
                                {{ $sorotan->status?->label() ?? $sorotan->status }}
                            </span>
                        </div>

                        <div class="p-6">
                            @php $pertandinganHero = $pertandinganTerdekat->first(); @endphp
                            @if($pertandinganHero)
                                <p class="text-[13px] font-semibold uppercase tracking-wide text-muted">Laga berikutnya</p>
                                <div class="mt-3 rounded-2xl border border-line surface-muted p-5">
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="min-w-0 flex-1 text-sm font-semibold leading-tight text-ink [overflow-wrap:anywhere]">{{ $pertandinganHero->teamA?->nama ?? 'TBD' }}</span>
                                        <span class="chip">vs</span>
                                        <span class="min-w-0 flex-1 text-right text-sm font-semibold leading-tight text-ink [overflow-wrap:anywhere]">{{ $pertandinganHero->teamB?->nama ?? 'TBD' }}</span>
                                    </div>
                                    <div class="mt-4 flex items-center justify-center gap-4 text-[13px] text-muted">
                                        @if($pertandinganHero->tanggal)
                                            <span>{{ $pertandinganHero->tanggal->translatedFormat('d M Y') }}</span>
                                        @endif
                                        @if($jam($pertandinganHero))
                                            <span class="inline-flex items-center gap-1">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                                {{ $jam($pertandinganHero) }} WITA
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <p class="text-[13px] font-semibold uppercase tracking-wide text-muted">Ringkasan penyelenggaraan</p>
                                <dl class="mt-3 grid grid-cols-2 gap-3">
                                    <div class="rounded-xl border border-line surface-muted p-4">
                                        <dt class="text-[13px] text-muted">Peserta</dt>
                                        <dd class="stat-value mt-1 font-display text-xl text-ink">{{ $sorotan->teams()->count() }}</dd>
                                    </div>
                                    <div class="rounded-xl border border-line surface-muted p-3">
                                        <dt class="text-[13px] text-muted">Pertandingan</dt>
                                        <dd class="stat-value mt-0.5 font-display text-lg text-ink">{{ $sorotan->matches()->count() }}</dd>
                                    </div>
                                </dl>
                            @endif

                            <div class="mt-5 flex flex-wrap items-center gap-x-4 gap-y-1 text-[13px] text-muted">
                                @if($sorotan->tanggal_mulai)
                                    <span>{{ $sorotan->tanggal_mulai->translatedFormat('d M Y') }}@if($sorotan->tanggal_selesai) &ndash; {{ $sorotan->tanggal_selesai->translatedFormat('d M Y') }}@endif</span>
                                @endif
                                @if($sorotan->lokasi)
                                    <span class="inline-flex items-center gap-1">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.714 4.5 10.5a7.5 7.5 0 0 1 15 0Z" /></svg>
                                        {{ $sorotan->lokasi }}
                                    </span>
                                @endif
                            </div>

                            <a href="{{ route('public.tournaments.show', $sorotan) }}" class="btn btn-primary mt-5 w-full py-3 text-base">Lihat Turnamen</a>
                        </div>
                    @else
                        <div class="relative overflow-hidden p-8">
                            <div class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full opacity-40" style="background-image: var(--grad-soft);"></div>
                            <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl text-white shadow-sm" style="background-image: var(--grad);">
                                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228" /></svg>
                            </span>
                            <h3 class="relative mt-5 font-display text-lg font-bold text-ink">Belum ada turnamen terbit</h3>
                            <p class="relative mt-2 text-sm leading-relaxed text-muted">
                                Jadwal pertandingan, klasemen, dan statistik akan tampil di sini setelah pengelola
                                mempublikasikan turnamen.
                            </p>
                            @auth
                                @if(auth()->user()->isAdmin())
                                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline mt-5">Buka Dashboard Admin</a>
                                @endif
                            @endauth
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    @if(!$adaTurnamen)
        {{-- ============================ KOSONG ============================ --}}
        <section class="app-container py-16">
            <div class="mx-auto max-w-2xl rounded-3xl border border-line surface-muted px-8 py-12 text-center">
                <span class="mx-auto inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-surface text-muted shadow-sm">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872" /></svg>
                </span>
                <h2 class="mt-5 font-display text-2xl font-bold text-ink">Belum ada turnamen yang dipublikasikan</h2>
                <p class="mx-auto mt-3 max-w-md text-base leading-relaxed text-muted">
                    Saat ini belum ada turnamen yang tampil untuk publik. Turnamen akan muncul di sini setelah
                    pengelola mengaktifkannya melalui dashboard.
                </p>
                <a href="{{ route('public.tournaments.index') }}" class="btn btn-outline mt-6">Lihat Daftar Turnamen</a>
            </div>
        </section>
    @else
        {{-- ============================ TURNAMEN SOROTAN ============================ --}}
        @if($sorotan)
            <section class="app-container pt-14 lg:pt-16">
                <div class="card panel-grad relative overflow-hidden">
                    <div class="pitch-pattern pointer-events-none absolute inset-0 opacity-25"></div>
                    <div class="relative grid gap-8 p-7 sm:p-9 lg:grid-cols-[1.4fr_1fr] lg:items-center lg:gap-12 lg:p-10">
                        <div>
                            <span class="inline-flex items-center gap-2 rounded-full bg-white/20 px-3 py-1 text-[13px] font-semibold text-white">
                                <span class="h-1.5 w-1.5 rounded-full bg-white"></span>
                                Turnamen Sorotan
                            </span>
                            <h2 class="mt-4 font-display text-3xl font-bold leading-tight text-white sm:text-4xl">{{ $sorotan->nama }}</h2>
                            <p class="mt-3 max-w-2xl text-base leading-relaxed text-white/85">{{ $sorotan->deskripsi ?: ($sorotan->cabang_olahraga ?? 'Turnamen olahraga') }}</p>
                            <div class="mt-4 flex flex-wrap gap-x-6 gap-y-1.5 text-[14px] text-white/85">
                                @if($sorotan->tanggal_mulai)
                                    <span>{{ $sorotan->tanggal_mulai->translatedFormat('d M') }}@if($sorotan->tanggal_selesai) &ndash; {{ $sorotan->tanggal_selesai->translatedFormat('d M Y') }}@endif</span>
                                @endif
                                @if($sorotan->lokasi)<span>{{ $sorotan->lokasi }}</span>@endif
                            </div>
                            <div class="mt-7 flex flex-wrap gap-3">
                                <a href="{{ route('public.tournaments.show', $sorotan) }}" class="btn btn-invert px-5 py-3 text-base">Ringkasan</a>
                                <a href="{{ route('public.tournaments.schedule', $sorotan) }}" class="btn border border-white/40 bg-white/10 px-5 py-3 text-base text-white hover:bg-white/20">Jadwal</a>
                                <a href="{{ route('public.tournaments.standings', $sorotan) }}" class="btn border border-white/40 bg-white/10 px-5 py-3 text-base text-white hover:bg-white/20">Klasemen</a>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-2xl border border-white/20 bg-white/10 p-5">
                                <p class="text-[13px] font-medium text-white/75">Tim Peserta</p>
                                <p class="stat-value mt-1 font-display text-3xl text-white">{{ $sorotan->teams()->count() }}</p>
                            </div>
                            <div class="rounded-2xl border border-white/20 bg-white/10 p-5">
                                <p class="text-[13px] font-medium text-white/75">Pertandingan</p>
                                <p class="stat-value mt-1 font-display text-3xl text-white">{{ $sorotan->matches()->count() }}</p>
                            </div>
                            <div class="rounded-2xl border border-white/20 bg-white/10 p-5">
                                <p class="text-[13px] font-medium text-white/75">Laga Selesai</p>
                                <p class="stat-value mt-1 font-display text-3xl text-white">{{ $sorotan->matches()->where('status', 'selesai')->count() }}</p>
                            </div>
                            <div class="rounded-2xl border border-white/20 bg-white/10 p-5">
                                <p class="text-[13px] font-medium text-white/75">Grup</p>
                                <p class="stat-value mt-1 font-display text-3xl text-white">{{ $sorotan->groups()->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- ============================ LAGA & HASIL ============================ --}}
        <section class="app-container py-14 lg:py-16">
            <div class="grid gap-6 lg:grid-cols-2 lg:gap-8">
                <div class="card p-6 sm:p-7">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="font-display text-xl font-bold text-ink">Pertandingan Terdekat</h2>
                        @if($tujuan)
                            <a href="{{ route('public.tournaments.schedule', $tujuan) }}" class="text-sm font-medium text-primary hover:opacity-80">Semua jadwal</a>
                        @endif
                    </div>
                    <div class="mt-5 space-y-3">
                        @forelse($pertandinganTerdekat as $match)
                            <a href="{{ route('public.tournaments.match', [$match->tournament, $match]) }}" class="match-card block p-4">
                                <div class="flex items-center justify-between gap-2 text-[13px] text-muted">
                                    <span class="truncate">{{ $match->tournament?->nama }} @if($match->group) &middot; {{ $match->group->nama }} @endif</span>
                                    <span class="badge badge-primary shrink-0">{{ $match->status?->label() ?? $match->status }}</span>
                                </div>
                                <div class="mt-2.5 flex items-center gap-3">
                                    <span class="min-w-0 flex-1 text-[15px] font-semibold leading-tight text-ink [overflow-wrap:anywhere]">{{ $match->teamA?->nama ?? 'TBD' }}</span>
                                    <span class="text-[13px] text-muted">vs</span>
                                    <span class="min-w-0 flex-1 text-right text-[15px] font-semibold leading-tight text-ink [overflow-wrap:anywhere]">{{ $match->teamB?->nama ?? 'TBD' }}</span>
                                </div>
                                <div class="mt-2.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-[13px] text-muted">
                                    @if($match->tanggal)<span>{{ $match->tanggal->translatedFormat('d M Y') }}</span>@endif
                                    @if($jam($match))<span>{{ $jam($match) }} WITA</span>@endif
                                    @if($match->lokasi)<span class="truncate">&middot; {{ $match->lokasi }}</span>@endif
                                </div>
                            </a>
                        @empty
                            <div class="rounded-xl border border-dashed border-line px-5 py-7 text-center text-sm text-muted">
                                Belum ada jadwal pertandingan terdekat.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="card p-6 sm:p-7">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="font-display text-xl font-bold text-ink">Hasil Terbaru</h2>
                        @if($tujuan)
                            <a href="{{ route('public.tournaments.results', $tujuan) }}" class="text-sm font-medium text-primary hover:opacity-80">Semua hasil</a>
                        @endif
                    </div>
                    <div class="mt-5 space-y-3">
                        @forelse($hasilTerbaru as $match)
                            <a href="{{ route('public.tournaments.match', [$match->tournament, $match]) }}" class="match-card block p-4">
                                <div class="flex items-center justify-between gap-2 text-[13px] text-muted">
                                    <span class="truncate">{{ $match->tournament?->nama }}</span>
                                    @if($match->tanggal)<span class="shrink-0">{{ $match->tanggal->translatedFormat('d M Y') }}</span>@endif
                                </div>
                                <div class="mt-2 flex items-center gap-3">
                                    <span class="min-w-0 flex-1 text-sm font-semibold {{ $match->pemenang_id === $match->tim_a_id ? 'text-ink' : 'text-muted' }} [overflow-wrap:anywhere]">{{ $match->teamA?->nama ?? 'TBD' }}</span>
                                    <span class="score-pill shrink-0">{{ $match->skorLabel() }}</span>
                                    <span class="min-w-0 flex-1 text-right text-sm font-semibold {{ $match->pemenang_id === $match->tim_b_id ? 'text-ink' : 'text-muted' }} [overflow-wrap:anywhere]">{{ $match->teamB?->nama ?? 'TBD' }}</span>
                                </div>
                            </a>
                        @empty
                            <div class="rounded-xl border border-dashed border-line px-5 py-6 text-center text-sm text-muted">
                                Belum ada hasil pertandingan yang tercatat.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        {{-- ============================ AKSES CEPAT ============================ --}}
        @if($tujuan)
            <section class="app-container pb-14 lg:pb-16">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h2 class="font-display text-xl font-bold text-ink">Akses Cepat</h2>
                        <p class="mt-1 text-sm text-muted">Pintasan menuju informasi {{ $tujuan->nama }}.</p>
                    </div>
                </div>
                <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                    @foreach($pintasan as $item)
                        <a href="{{ route($item['route'], $tujuan) }}" class="card card-interactive group flex items-center gap-3 p-4 sm:flex-col sm:items-start sm:gap-0">
                            <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl surface-muted text-primary">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['ikon'] }}" /></svg>
                            </span>
                            <span class="min-w-0 sm:mt-3">
                                <span class="block text-[15px] font-semibold text-ink">{{ $item['label'] }}</span>
                                <span class="mt-0.5 block text-[13px] text-muted">{{ $item['desc'] }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ============================ TURNAMEN TERBARU ============================ --}}
        <section class="app-container pb-16">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="font-display text-xl font-bold text-ink">Turnamen Terbaru</h2>
                    <p class="mt-1 text-sm text-muted">Turnamen yang telah dipublikasikan untuk publik.</p>
                </div>
                <a href="{{ route('public.tournaments.index') }}" class="text-sm font-medium text-primary hover:opacity-80">Lihat semua</a>
            </div>

            <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($tournaments as $tournament)
                    <article class="card card-interactive flex h-full flex-col p-6">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="badge badge-primary">{{ $tournament->cabang_olahraga ?? 'Turnamen' }}</span>
                            <span class="badge badge-muted">{{ $tournament->status?->label() ?? $tournament->status }}</span>
                        </div>
                        <h3 class="mt-3 font-display text-lg font-bold leading-tight text-ink">{{ $tournament->nama }}</h3>
                        <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-muted">{{ $tournament->deskripsi ?: 'Belum ada deskripsi.' }}</p>
                        <dl class="mt-4 space-y-2 text-[14px]">
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-muted">Tanggal</dt>
                                <dd class="text-right text-ink">{{ optional($tournament->tanggal_mulai)->translatedFormat('d M Y') ?? '-' }} &ndash; {{ optional($tournament->tanggal_selesai)->translatedFormat('d M Y') ?? '-' }}</dd>
                            </div>
                            @if($tournament->lokasi)
                                <div class="flex items-center justify-between gap-3">
                                    <dt class="text-muted">Lokasi</dt>
                                    <dd class="truncate text-right text-ink">{{ $tournament->lokasi }}</dd>
                                </div>
                            @endif
                        </dl>
                        <div class="mt-auto pt-5">
                            <a href="{{ route('public.tournaments.show', $tournament) }}" class="btn btn-outline w-full">Lihat Detail</a>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
@endsection
