@extends('layouts.admin')

@section('title', ($tournament->nama ?? 'Kelola') . ' - Kelola Turnamen')

@section('breadcrumb')
    <span class="breadcrumb">
        <a href="{{ route('admin.tournaments.index') }}">Turnamen</a>
        <span class="text-muted/60">/</span>
        <span class="text-ink">{{ $tournament->nama }}</span>
    </span>
@endsection

@section('content')
    <div class="card mb-5 overflow-hidden">
        <div class="grad-bar" style="border-radius:0"></div>
        <div class="flex flex-wrap items-start justify-between gap-4 p-5">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="page-title !text-xl">{{ $tournament->nama }}</h1>
                    <span class="badge badge-primary">{{ $tournament->status?->label() ?? $tournament->status?->value ?? $tournament->status }}</span>
                </div>
                <p class="mt-1.5 text-sm text-muted">
                    {{ $tournament->cabang_olahraga ?? 'Cabang olahraga belum ditentukan' }}
                    @if($tournament->lokasi)
                        &middot; {{ $tournament->lokasi }}
                    @endif
                    @if($tournament->tanggal_mulai)
                        &middot; {{ $tournament->tanggal_mulai->format('d M Y') }}
                        @if($tournament->tanggal_selesai && ! $tournament->tanggal_mulai->isSameDay($tournament->tanggal_selesai))
                            &ndash; {{ $tournament->tanggal_selesai->format('d M Y') }}
                        @endif
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('public.tournaments.show', $tournament) }}" target="_blank" class="btn btn-outline text-sm">Lihat Publik</a>
                <a href="{{ route('admin.manage.matches.index', $tournament) }}" class="btn btn-primary text-sm">Kelola Pertandingan</a>
            </div>
        </div>
    </div>

    @yield('manage')
@endsection
