@extends('layouts.manage')

@section('manage')
    <div class="page-header">
        <div>
            <h1 class="page-title">Jadwal &amp; Pertandingan</h1>
            <p class="page-subtitle">Kelola jadwal, status, dan hasil pertandingan turnamen ini.</p>
        </div>
        @if($rounds->isNotEmpty())
            <a href="{{ route('admin.manage.matches.create', $tournament) }}" class="btn btn-primary">Jadwalkan Pertandingan</a>
        @endif
    </div>

    @if($rounds->isEmpty())
        <div class="empty-state">
            <span class="empty-state-icon">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
            </span>
            <p class="font-medium text-ink">Belum ada putaran</p>
            <p>Buat putaran terlebih dahulu pada menu Grup &amp; Putaran.</p>
            <a href="{{ route('admin.manage.rounds.index', $tournament) }}" class="btn btn-primary mt-1">Buka Grup &amp; Putaran</a>
        </div>
    @else
        <form method="GET" class="card mb-5 p-4">
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                <div class="field">
                    <label class="field-label" for="f-round">Putaran</label>
                    <select id="f-round" name="round" class="select">
                        <option value="">Semua putaran</option>
                        @foreach($rounds as $round)
                            <option value="{{ $round->id }}" @selected($roundId == $round->id)>Putaran {{ $round->nomor }}: {{ $round->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label class="field-label" for="f-group">Grup</label>
                    <select id="f-group" name="group" class="select">
                        <option value="">Semua grup</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}" @selected($groupId == $group->id)>{{ $group->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label class="field-label" for="f-tanggal">Tanggal</label>
                    <input id="f-tanggal" type="date" name="tanggal" value="{{ $tanggal }}" class="input">
                </div>
                <div class="field">
                    <label class="field-label" for="f-status">Status</label>
                    <select id="f-status" name="status" class="select">
                        <option value="">Semua status</option>
                        @foreach(\App\Enums\MatchStatus::cases() as $st)
                            <option value="{{ $st->value }}" @selected($status === $st->value)>{{ $st->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label class="field-label" for="f-q">Cari tim</label>
                    <input id="f-q" type="search" name="q" value="{{ $cari }}" class="input" placeholder="Nama tim...">
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2">
                <button type="submit" class="btn btn-primary text-sm">Terapkan Filter</button>
                @if($adaFilter)
                    <a href="{{ route('admin.manage.matches.index', $tournament) }}" class="btn btn-ghost text-sm">Reset</a>
                @endif
                <span class="ml-auto text-xs text-muted">{{ $matches->count() }} pertandingan</span>
            </div>
        </form>

        @php
            $kelompok = $matches->groupBy(fn ($m) => $m->tanggal?->format('Y-m-d') ?? 'tanpa-tanggal');
        @endphp

        <div class="space-y-7">
            @forelse($kelompok as $tanggalKey => $daftar)
                <section>
                    <div class="mb-3 flex flex-wrap items-center gap-3">
                        <h2 class="section-title">
                            @if($tanggalKey === 'tanpa-tanggal')
                                Tanpa tanggal
                            @else
                                {{ \Carbon\Carbon::parse($tanggalKey)->locale('id')->translatedFormat('l, d F Y') }}
                            @endif
                        </h2>
                        <span class="badge badge-muted">{{ $daftar->count() }} pertandingan</span>
                    </div>

                    <div class="space-y-2.5">
                        @foreach($daftar as $match)
                            @php
                                $st = $match->status?->value ?? $match->status;
                                $tampilSkor = in_array($st, ['selesai', 'berlangsung'], true);
                                $badge = $st === 'selesai' ? 'badge badge-success' : ($st === 'berlangsung' ? 'badge badge-primary' : ($st === 'dibatalkan' ? 'badge badge-muted' : 'badge badge-muted'));
                            @endphp
                            <article class="card card-interactive p-4">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                                    <div class="flex shrink-0 items-center gap-3 sm:w-28">
                                        <div class="flex flex-col">
                                            <span class="font-display text-lg font-semibold text-ink">{{ $match->jam ?: '--:--' }}</span>
                                            <span class="text-xs text-muted">#{{ $match->nomor_pertandingan ?? '–' }}</span>
                                        </div>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="mb-1.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-muted">
                                            <span>{{ $match->babakLabel() }}</span>
                                            @if($match->group)<span>&middot; {{ $match->group->nama }}</span>@endif
                                            @if($match->referee)<span>&middot; Wasit: {{ $match->referee->nama }}</span>@endif
                                            @if($match->lokasi)&middot; {{ $match->lokasi }}@endif
                                        </div>
                                        <div class="space-y-1">
                                            <div class="flex items-center justify-between gap-3">
                                                <span class="truncate font-medium text-ink">{{ $match->teamA?->nama ?? 'TBD' }}</span>
                                                <span class="font-display text-lg font-semibold {{ $tampilSkor ? 'text-ink' : 'text-muted' }}">{{ $tampilSkor ? $match->skor_a : '–' }}</span>
                                            </div>
                                            <div class="flex items-center justify-between gap-3">
                                                <span class="truncate font-medium text-ink">{{ $match->teamB?->nama ?? 'TBD' }}</span>
                                                <span class="font-display text-lg font-semibold {{ $tampilSkor ? 'text-ink' : 'text-muted' }}">{{ $tampilSkor ? $match->skor_b : '–' }}</span>
                                            </div>
                                        </div>
                                        @if($match->isGugur() && $tampilSkor && $match->skor_penalti_a !== null)
                                            <p class="mt-1 text-xs text-muted">Penalti {{ $match->skor_penalti_a }} - {{ $match->skor_penalti_b }}</p>
                                        @endif
                                    </div>

                                    <div class="flex flex-wrap items-center gap-2 sm:flex-col sm:items-stretch">
                                        <span class="{{ $badge }} sm:mx-auto">{{ $match->status?->label() ?? $st }}</span>
                                        <a href="{{ route('admin.manage.matches.edit', [$tournament, $match]) }}" class="btn btn-primary !px-3 !py-1.5 text-xs">Input Hasil</a>
                                        <details class="relative">
                                            <summary class="btn btn-outline !px-3 !py-1.5 text-xs cursor-pointer list-none">Lainnya</summary>
                                            <div class="absolute right-0 z-20 mt-2 w-60 rounded-xl border border-line bg-surface p-2 shadow-lg">
                                                <a href="{{ route('admin.manage.matches.edit', [$tournament, $match]) }}" class="nav-item !py-2 text-sm">Detail pertandingan</a>
                                                <form method="POST" action="{{ route('admin.manage.matches.status', [$tournament, $match]) }}" class="px-1 py-1.5">
                                                    @csrf
                                                    <label class="field-label mb-1">Ubah status</label>
                                                    <select name="status" onchange="this.form.submit()" class="select !py-1.5 text-sm">
                                                        @foreach(\App\Enums\MatchStatus::cases() as $mstatus)
                                                            <option value="{{ $mstatus->value }}" @selected($match->status === $mstatus)>{{ $mstatus->label() }}</option>
                                                        @endforeach
                                                    </select>
                                                </form>
                                                <form method="POST" action="{{ route('admin.manage.matches.destroy', [$tournament, $match]) }}" onsubmit="return confirm('Hapus pertandingan ini? Tindakan ini tidak dapat dibatalkan.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="nav-item w-full !py-2 text-sm text-red-600 hover:bg-red-50">Hapus pertandingan</button>
                                                </form>
                                            </div>
                                        </details>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @empty
                <div class="empty-state">
                    <span class="empty-state-icon">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    </span>
                    <p class="font-medium text-ink">Tidak ada pertandingan</p>
                    <p>Tidak ada pertandingan yang cocok dengan filter saat ini.</p>
                    @if($adaFilter)
                        <a href="{{ route('admin.manage.matches.index', $tournament) }}" class="btn btn-outline mt-1">Reset filter</a>
                    @endif
                </div>
            @endforelse
        </div>
    @endif
@endsection
