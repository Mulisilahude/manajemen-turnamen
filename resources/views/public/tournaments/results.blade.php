@extends('layouts.app')

@section('title', 'Hasil - '.$tournament->nama)

@section('content')
    @include('public.tournaments.partials.nav', ['tournament' => $tournament])
    <div class="app-container max-w-4xl py-8">
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-ink">Hasil Pertandingan</h1>
            <p class="mt-1 text-sm text-muted">Daftar pertandingan yang telah selesai.</p>
        </div>

        <div class="space-y-3">
            @forelse($matches as $match)
                <a href="{{ route('public.tournaments.match', [$tournament, $match]) }}" class="match-card block p-4 sm:p-5">
                    <div class="flex items-center justify-between gap-3 text-xs text-muted">
                        <span>{{ $match->babakLabel() }}@if($match->group) &middot; {{ $match->group->nama }}@endif</span>
                        @if($match->tanggal)<span>{{ $match->tanggal->translatedFormat('d M Y') }}</span>@endif
                    </div>
                    <div class="mt-3 flex items-center gap-3">
                        <span class="flex-1 truncate text-right text-sm font-semibold {{ $match->pemenang_id === $match->tim_a_id ? 'text-ink' : 'text-muted' }}">
                            @if($match->pemenang_id === $match->tim_a_id)<span class="mr-1 text-xs font-semibold text-success">M</span>@endif{{ $match->teamA?->nama ?? 'TBD' }}
                        </span>
                        <span class="score-pill shrink-0">{{ $match->skorLabel() }}</span>
                        <span class="flex-1 truncate text-sm font-semibold {{ $match->pemenang_id === $match->tim_b_id ? 'text-ink' : 'text-muted' }}">
                            {{ $match->teamB?->nama ?? 'TBD' }}@if($match->pemenang_id === $match->tim_b_id)<span class="ml-1 text-xs font-semibold text-success">M</span>@endif
                        </span>
                    </div>
                    @if($match->skor_penalti_a !== null)
                        <div class="mt-2 text-center text-xs text-muted">Adu penalti {{ $match->skor_penalti_a }} - {{ $match->skor_penalti_b }}</div>
                    @endif
                </a>
            @empty
                <div class="card border border-dashed p-10 text-center text-sm text-muted">Belum ada hasil pertandingan.</div>
            @endforelse
        </div>
    </div>
@endsection
