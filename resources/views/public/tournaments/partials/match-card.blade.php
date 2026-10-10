@php
    $st = $match->status?->value ?? $match->status;
    $labelStatus = $match->status?->label() ?? $st;
    $badgeKelas = $match->status?->badge() ?? 'badge badge-muted';
    $hari = $match->tanggal?->locale('id')->translatedFormat('l, d F Y');
    $jam = $match->jam ? str_replace(':', '.', substr($match->jam, 0, 5)).' WITA' : null;
    $babak = $match->round ? 'Putaran '.$match->round->nomor : $match->babakLabel();
    $konteks = $babak.($match->group ? ' · '.$match->group->nama : '');
    $punyaSkor = $match->punyaSkor();

    $inisial = function ($team) {
        if (! $team) {
            return '?';
        }

        if (filled($team->singkatan)) {
            return mb_strtoupper(mb_substr(trim($team->singkatan), 0, 3));
        }

        $huruf = collect(preg_split('/\s+/', trim((string) $team->nama)))
            ->filter()
            ->map(fn ($kata) => mb_strtoupper(mb_substr($kata, 0, 1)))
            ->take(2)
            ->implode('');

        return $huruf !== '' ? $huruf : '?';
    };
@endphp

<a href="{{ route('public.tournaments.match', [$tournament, $match]) }}"
   class="match-card block p-4 sm:p-5"
   data-match-card
   data-match-id="{{ $match->id }}"
   data-match-status="{{ $st }}">
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-[minmax(0,0.9fr)_minmax(0,1.4fr)_minmax(0,0.9fr)] sm:items-center sm:gap-5">
        {{-- KIRI: hari dan waktu --}}
        <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-0.5 border-b border-line pb-3 sm:flex-col sm:items-start sm:border-0 sm:pb-0">
            <span class="font-display text-[0.95rem] font-semibold leading-tight text-ink sm:text-base">
                {{ $hari ?? 'Belum dijadwalkan' }}
            </span>
            @if($jam)
                <span class="text-xs font-medium tabular-nums text-muted sm:mt-0.5">{{ $jam }}</span>
            @endif
        </div>

        {{-- TENGAH: kedua tim --}}
        <div class="flex items-center gap-2 sm:gap-4">
            <div class="flex min-w-0 flex-1 items-center justify-end gap-2">
                <span class="min-w-0 text-right text-sm font-semibold leading-tight text-ink [overflow-wrap:anywhere] sm:text-[0.95rem]">{{ $match->teamA?->nama ?? 'TBD' }}</span>
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-line bg-surface-2 text-[0.7rem] font-semibold uppercase text-muted sm:h-10 sm:w-10">{{ $inisial($match->teamA) }}</span>
            </div>

            <span class="shrink-0 px-0.5 text-[0.7rem] font-semibold uppercase tracking-wide text-muted">vs</span>

            <div class="flex min-w-0 flex-1 items-center gap-2">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-line bg-surface-2 text-[0.7rem] font-semibold uppercase text-muted sm:h-10 sm:w-10">{{ $inisial($match->teamB) }}</span>
                <span class="min-w-0 text-left text-sm font-semibold leading-tight text-ink [overflow-wrap:anywhere] sm:text-[0.95rem]">{{ $match->teamB?->nama ?? 'TBD' }}</span>
            </div>
        </div>

        {{-- KANAN: grup, skor, dan status (tampil di semua ukuran) --}}
        <div class="flex flex-col gap-2 border-t border-line pt-3 sm:items-end sm:border-0 sm:pt-0 sm:text-right">
            <span class="min-w-0 text-xs font-medium text-muted sm:text-[0.8rem]">{{ $konteks }}</span>
            <div class="flex items-center justify-between gap-2 sm:flex-col sm:items-end">
                <span class="{{ $punyaSkor ? 'score-pill' : 'score-pill-muted' }}" data-match-field="score">{{ $match->teksSkor() }}</span>
                <span class="{{ $badgeKelas }}" data-match-field="status">{{ $labelStatus }}</span>
            </div>
        </div>
    </div>
</a>
