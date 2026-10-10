@extends('layouts.app')

@section('title', 'Tim - '.$tournament->nama)

@section('content')
    @include('public.tournaments.partials.nav', ['tournament' => $tournament])
    <div class="app-container py-8">
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-ink">Tim Peserta</h1>
            <p class="mt-1 text-sm text-muted">{{ $teams->count() }} tim terdaftar pada turnamen ini.</p>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($teams as $team)
                <article class="card card-interactive flex items-center gap-4 p-5">
                    <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-xl text-base font-bold text-white" style="background-image: var(--grad);">
                        {{ \Illuminate\Support\Str::of($team->nama)->substr(0, 2)->upper() }}
                    </span>
                    <div class="min-w-0">
                        <h2 class="truncate font-semibold text-ink">{{ $team->nama }}</h2>
                        <p class="truncate text-sm text-muted">
                            @if($team->singkatan){{ $team->singkatan }} &middot; @endif{{ $team->players_count }} pemain
                        </p>
                        <p class="truncate text-xs text-muted">Pelatih: {{ $team->pelatih ?? '-' }}</p>
                    </div>
                </article>
            @empty
                <div class="card border border-dashed p-10 text-center text-sm text-muted sm:col-span-2 lg:col-span-3">Belum ada tim.</div>
            @endforelse
        </div>
    </div>
@endsection
