@extends('layouts.app')

@section('title', 'Pertandingan - '.$tournament->nama)

@php
    $jam = $match->jam ? substr($match->jam, 0, 5) : null;
@endphp

@section('content')
    @include('public.tournaments.partials.nav', ['tournament' => $tournament])
    <div class="app-container max-w-4xl py-8">
        <a href="{{ route('public.tournaments.schedule', $tournament) }}" class="mb-4 inline-flex items-center gap-1.5 text-sm text-muted transition hover:text-primary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            Kembali ke jadwal
        </a>

        <div class="card overflow-hidden">
            <div class="grad-bar"></div>
            <div class="p-6 sm:p-8">
                <div class="text-center text-xs text-muted">{{ $match->babakLabel() }}@if($match->group) &middot; {{ $match->group->nama }}@endif</div>
                <div class="mt-5 grid grid-cols-[1fr_auto_1fr] items-center gap-4">
                    <div class="text-right">
                        <div class="text-base font-semibold text-ink sm:text-lg">{{ $match->teamA?->nama ?? 'TBD' }}</div>
                        @if($match->pemenang_id === $match->tim_a_id)<span class="mt-1 inline-block text-xs font-medium text-success">Pemenang</span>@endif
                    </div>
                    <div class="text-center">
                        <div class="score-pill !min-w-[5rem] !px-4 !py-2 text-xl">{{ $match->skorLabel() }}</div>
                        <div class="mt-2 text-xs text-muted">{{ $match->status?->label() ?? $match->status }}</div>
                    </div>
                    <div>
                        <div class="text-base font-semibold text-ink sm:text-lg">{{ $match->teamB?->nama ?? 'TBD' }}</div>
                        @if($match->pemenang_id === $match->tim_b_id)<span class="mt-1 inline-block text-xs font-medium text-success">Pemenang</span>@endif
                    </div>
                </div>

                @if($match->skor_penalti_a !== null)
                    <p class="mt-4 text-center text-sm text-muted">Adu penalti: {{ $match->skor_penalti_a }} - {{ $match->skor_penalti_b }}</p>
                @endif

                <dl class="mt-7 grid gap-4 border-t border-line pt-6 text-sm sm:grid-cols-3">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-muted">Tanggal</dt>
                        <dd class="mt-1 text-ink">{{ $match->tanggal?->translatedFormat('d M Y') ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-muted">Waktu</dt>
                        <dd class="mt-1 text-ink">{{ $match->jam ? substr($match->jam, 0, 5).' WITA' : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-muted">Lokasi</dt>
                        <dd class="mt-1 text-ink">{{ $match->lokasi ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-muted">Wasit</dt>
                        <dd class="mt-1 text-ink">{{ $match->referee?->nama ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-muted">Pemenang</dt>
                        <dd class="mt-1 text-ink">{{ $match->winner?->nama ?? '-' }}</dd>
                    </div>
                </dl>

                @if($match->catatan)
                    <p class="mt-5 rounded-xl surface-muted p-4 text-sm text-muted">{{ $match->catatan }}</p>
                @endif
            </div>
        </div>

        <div class="card mt-6 p-6">
            <h2 class="text-base font-semibold text-ink">Kejadian Pertandingan</h2>
            <div class="mt-4 divide-y divide-slate-100">
                @forelse($match->events as $event)
                    <div class="flex items-center justify-between gap-3 py-2.5 text-sm">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="w-10 shrink-0 text-right text-xs font-medium text-muted">{{ $event->menit ? $event->menit."'" : '-' }}</span>
                            <span class="badge badge-muted shrink-0">{{ $event->tipe?->label() }}</span>
                            <span class="truncate text-ink">{{ $event->player?->nama ?? 'Tanpa nama' }}</span>
                        </div>
                        <span class="shrink-0 truncate text-xs text-muted">{{ $event->team?->nama }}@if($event->assistPlayer) &middot; assist {{ $event->assistPlayer->nama }}@endif</span>
                    </div>
                @empty
                    <p class="py-4 text-sm text-muted">Belum ada kejadian tercatat.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
