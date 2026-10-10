@extends('layouts.app')

@section('title', 'Bagan - '.$tournament->nama)

@section('content')
    @include('public.tournaments.partials.nav', ['tournament' => $tournament])
    <div class="app-container py-8">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold text-ink">Bagan Fase Gugur</h1>
                <p class="mt-1 text-sm text-muted">Hasil dan pertandingan babak gugur.</p>
            </div>
            @if($juara)
                <span class="badge panel-grad !border-transparent !px-4 !py-1.5 !text-sm">Juara: {{ $juara->nama }}</span>
            @endif
        </div>

        @forelse($roundsGugur as $round)
            <div class="mb-8">
                <h2 class="mb-3 text-base font-semibold text-ink">{{ $round->nama }}</h2>
                <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($round->matches as $match)
                        <a href="{{ route('public.tournaments.match', [$tournament, $match]) }}" class="match-card block p-4">
                            <div class="flex items-center justify-between gap-2 text-sm">
                                <span class="{{ $match->pemenang_id === $match->tim_a_id ? 'font-semibold text-ink' : 'text-muted' }}">{{ $match->teamA?->nama ?? 'TBD' }}</span>
                                <span class="score-pill !min-w-0 !px-2 !py-0.5 text-xs">{{ $match->skor_a ?? '-' }}</span>
                            </div>
                            <div class="mt-2 flex items-center justify-between gap-2 text-sm">
                                <span class="{{ $match->pemenang_id === $match->tim_b_id ? 'font-semibold text-ink' : 'text-muted' }}">{{ $match->teamB?->nama ?? 'TBD' }}</span>
                                <span class="score-pill !min-w-0 !px-2 !py-0.5 text-xs">{{ $match->skor_b ?? '-' }}</span>
                            </div>
                            @if($match->skor_penalti_a !== null)
                                <div class="mt-2 text-xs text-muted">Adu penalti: {{ $match->skor_penalti_a }} - {{ $match->skor_penalti_b }}</div>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="card border border-dashed p-10 text-center text-sm text-muted">Belum ada fase gugur.</div>
        @endforelse
    </div>
@endsection
