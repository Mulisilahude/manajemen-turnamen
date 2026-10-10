@extends('layouts.manage')

@section('manage')
    @php
        $editing = isset($match) && $match && $match->exists;
    @endphp

    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $editing ? 'Input Hasil Pertandingan' : 'Jadwalkan Pertandingan' }}</h1>
            <p class="page-subtitle">
                @if($editing)
                    Perbarui skor, kejadian, dan detail pertandingan.
                @else
                    Tentukan putaran, tim, dan jadwal pertandingan baru.
                @endif
            </p>
        </div>
        <a href="{{ route('admin.manage.matches.index', $tournament) }}" class="btn btn-outline">Kembali ke Jadwal</a>
    </div>

    @if($editing)
        @php
            $st = $match->status?->value ?? $match->status;
            $badge = $st === 'selesai' ? 'badge badge-success' : ($st === 'berlangsung' ? 'badge badge-primary' : 'badge badge-muted');
        @endphp
        <div class="card mb-5 overflow-hidden">
            <div class="grad-bar" style="border-radius:0"></div>
            <div class="p-5">
                <div class="mb-4 flex flex-wrap items-center justify-center gap-2 text-xs text-muted">
                    <span>{{ $match->babakLabel() }}</span>
                    @if($match->group)&middot; <span>{{ $match->group->nama }}</span>@endif
                    @if($match->tanggal)&middot; <span>{{ $match->tanggal->format('d M Y') }}</span>@endif
                    @if($match->jam)&middot; <span>{{ $match->jam }}</span>@endif
                    @if($match->lokasi)&middot; <span>{{ $match->lokasi }}</span>@endif
                </div>
                <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-3 sm:gap-6">
                    <p class="truncate text-right font-display text-base font-semibold text-ink sm:text-lg">{{ $match->teamA?->nama ?? 'TBD' }}</p>
                    <div class="flex flex-col items-center">
                        <p class="font-display text-3xl font-bold tabular-nums text-ink sm:text-4xl">{{ $match->skor_a }} <span class="text-muted">:</span> {{ $match->skor_b }}</p>
                        @if($match->isGugur() && $match->skor_penalti_a !== null)
                            <p class="mt-1 text-xs text-muted">Penalti {{ $match->skor_penalti_a }} - {{ $match->skor_penalti_b }}</p>
                        @endif
                    </div>
                    <p class="truncate font-display text-base font-semibold text-ink sm:text-lg">{{ $match->teamB?->nama ?? 'TBD' }}</p>
                </div>
                <div class="mt-4 flex justify-center">
                    <span class="{{ $badge }}">{{ $match->status?->label() ?? $st }}</span>
                </div>
            </div>
        </div>
    @endif

    <div class="grid gap-5 lg:grid-cols-3">
        @if($editing)
            <div class="space-y-5 lg:col-span-2">
                <div class="card p-5">
                    <h2 class="section-title">Input Skor</h2>
                    <form method="POST" action="{{ route('admin.manage.matches.skor', [$tournament, $match]) }}" class="mt-4 space-y-4">
                        @csrf
                        <div class="grid grid-cols-2 gap-3">
                            <div class="field">
                                <label class="field-label">{{ $match->teamA?->nama ?? 'Tim A' }}</label>
                                <input type="number" name="skor_a" value="{{ $match->skor_a }}" min="0" max="99" class="input text-center font-display text-lg">
                            </div>
                            <div class="field">
                                <label class="field-label">{{ $match->teamB?->nama ?? 'Tim B' }}</label>
                                <input type="number" name="skor_b" value="{{ $match->skor_b }}" min="0" max="99" class="input text-center font-display text-lg">
                            </div>
                        </div>
                        @if($match->isGugur())
                            <div class="grid grid-cols-2 gap-3">
                                <div class="field">
                                    <label class="field-label">Penalti A</label>
                                    <input type="number" name="skor_penalti_a" value="{{ $match->skor_penalti_a }}" class="input text-center">
                                </div>
                                <div class="field">
                                    <label class="field-label">Penalti B</label>
                                    <input type="number" name="skor_penalti_b" value="{{ $match->skor_penalti_b }}" class="input text-center">
                                </div>
                            </div>
                        @endif
                        <button type="submit" class="btn btn-primary">Simpan Skor</button>
                    </form>
                </div>

                <div class="card p-5">
                    <h2 class="section-title">Kejadian Pertandingan</h2>
                    <form method="POST" action="{{ route('admin.manage.events.store', [$tournament, $match]) }}" class="mt-4 grid gap-3 sm:grid-cols-2">
                        @csrf
                        <div class="field">
                            <label class="field-label">Jenis</label>
                            <select name="tipe" class="select">
                                @foreach(\App\Enums\EventType::cases() as $tipe)
                                    <option value="{{ $tipe->value }}">{{ $tipe->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label class="field-label">Tim</label>
                            <select name="team_id" class="select" required>
                                <option value="{{ $match->tim_a_id }}">{{ $match->teamA?->nama ?? 'Tim A' }}</option>
                                <option value="{{ $match->tim_b_id }}">{{ $match->teamB?->nama ?? 'Tim B' }}</option>
                            </select>
                        </div>
                        <div class="field">
                            <label class="field-label">Pencetak</label>
                            <select name="player_id" class="select">
                                <option value="">Pencetak (opsional)</option>
                                @foreach($players as $player)
                                    <option value="{{ $player->id }}">{{ $player->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label class="field-label">Assist</label>
                            <select name="assist_player_id" class="select">
                                <option value="">Assist (opsional)</option>
                                @foreach($players as $player)
                                    <option value="{{ $player->id }}">{{ $player->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field sm:col-span-2">
                            <label class="field-label">Menit</label>
                            <input type="number" name="menit" min="0" max="130" class="input" placeholder="Menit ke-">
                        </div>
                        <div class="sm:col-span-2">
                            <button type="submit" class="btn btn-outline">Tambah Kejadian</button>
                        </div>
                    </form>

                    <div class="mt-5 divide-y divide-[color:var(--line)]">
                        @forelse($match->events as $event)
                            <div class="flex items-center justify-between gap-3 py-3 text-sm">
                                <div class="min-w-0">
                                    <div class="font-medium text-ink">
                                        {{ $event->tipe?->label() }}
                                        @if($event->menit)<span class="text-muted">· menit {{ $event->menit }}</span>@endif
                                    </div>
                                    <div class="text-xs text-muted">
                                        {{ $event->player?->nama ?? 'Tanpa nama' }}
                                        @if($event->assistPlayer) (assist: {{ $event->assistPlayer->nama }})@endif
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('admin.manage.events.destroy', [$tournament, $match, $event]) }}" onsubmit="return confirm('Hapus kejadian ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger !px-2.5 !py-1 text-xs">Hapus</button>
                                </form>
                            </div>
                        @empty
                            <p class="py-4 text-sm text-muted">Belum ada kejadian.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif

        <div class="{{ $editing ? 'space-y-5' : 'space-y-5 lg:col-span-3' }}">
            <div class="card p-5">
                <h2 class="section-title">{{ $editing ? 'Detail Pertandingan' : 'Informasi Pertandingan' }}</h2>
                <form method="POST" action="{{ $editing ? route('admin.manage.matches.update', [$tournament, $match]) : route('admin.manage.matches.store', $tournament) }}" class="mt-4 space-y-4">
                    @csrf
                    @if($editing)
                        @method('PUT')
                    @endif
                    <div class="fieldset fieldset-2">
                        <div class="field">
                            <label class="field-label">Putaran</label>
                            <select name="round_id" class="select" required>
                                <option value="">Pilih putaran</option>
                                @foreach($rounds as $round)
                                    <option value="{{ $round->id }}" @selected(old('round_id', $match->round_id ?? null) == $round->id)>Putaran {{ $round->nomor }}: {{ $round->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label class="field-label">Grup (opsional)</label>
                            <select name="group_id" class="select">
                                <option value="">Tanpa grup</option>
                                @foreach($rounds as $round)
                                    @foreach($round->groups as $group)
                                        <option value="{{ $group->id }}" @selected(old('group_id', $match->group_id ?? null) == $group->id)>{{ $round->nama }} · {{ $group->nama }}</option>
                                    @endforeach
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label class="field-label">Tim A</label>
                            <select name="tim_a_id" class="select" required>
                                <option value="">Pilih tim</option>
                                @foreach($teams as $team)
                                    <option value="{{ $team->id }}" @selected(old('tim_a_id', $match->tim_a_id ?? null) == $team->id)>{{ $team->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label class="field-label">Tim B</label>
                            <select name="tim_b_id" class="select" required>
                                <option value="">Pilih tim</option>
                                @foreach($teams as $team)
                                    <option value="{{ $team->id }}" @selected(old('tim_b_id', $match->tim_b_id ?? null) == $team->id)>{{ $team->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label class="field-label">Wasit</label>
                            <select name="referee_id" class="select">
                                <option value="">Belum ditentukan</option>
                                @foreach($referees as $referee)
                                    <option value="{{ $referee->id }}" @selected(old('referee_id', $match->wasit_id ?? null) == $referee->id)>{{ $referee->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label class="field-label">Status</label>
                            <select name="status" class="select">
                                @foreach(\App\Enums\MatchStatus::cases() as $status)
                                    <option value="{{ $status->value }}" @selected(old('status', $match->status?->value ?? 'dijadwalkan') === $status->value)>{{ $status->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label class="field-label">Tanggal</label>
                            <input type="date" name="tanggal" value="{{ old('tanggal', optional($match->tanggal ?? null)->format('Y-m-d')) }}" class="input">
                        </div>
                        <div class="field">
                            <label class="field-label">Jam</label>
                            <input type="time" name="jam" value="{{ old('jam', $match->jam ?? null) }}" class="input">
                        </div>
                        <div class="field">
                            <label class="field-label">Nomor Pertandingan</label>
                            <input type="number" name="nomor_pertandingan" value="{{ old('nomor_pertandingan', $match->nomor_pertandingan ?? null) }}" class="input">
                        </div>
                        <div class="field">
                            <label class="field-label">Babak</label>
                            <input type="text" name="babak" value="{{ old('babak', $match->babak ?? null) }}" class="input" placeholder="Contoh: Final">
                        </div>
                        <div class="field sm:col-span-2">
                            <label class="field-label">Lokasi</label>
                            <input type="text" name="lokasi" value="{{ old('lokasi', $match->lokasi ?? null) }}" class="input">
                        </div>
                        <div class="field sm:col-span-2">
                            <label class="field-label">Catatan</label>
                            <textarea name="catatan" rows="2" class="textarea">{{ old('catatan', $match->catatan ?? null) }}</textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">{{ $editing ? 'Simpan Detail' : 'Simpan Pertandingan' }}</button>
                </form>
            </div>

            @if($editing)
                <div class="card p-5">
                    <h2 class="section-title">Status Pertandingan</h2>
                    <form method="POST" action="{{ route('admin.manage.matches.status', [$tournament, $match]) }}" class="mt-3 flex flex-wrap items-center gap-2">
                        @csrf
                        <select name="status" class="select max-w-[12rem]">
                            @foreach(\App\Enums\MatchStatus::cases() as $mstatus)
                                <option value="{{ $mstatus->value }}" @selected($match->status === $mstatus)>{{ $mstatus->label() }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-outline">Ubah Status</button>
                    </form>
                </div>

                <div class="card p-5">
                    <h2 class="section-title">Zona Berbahaya</h2>
                    <p class="mt-2 text-sm text-muted">Menghapus pertandingan akan menghapus seluruh kejadian terkait secara permanen.</p>
                    <form method="POST" action="{{ route('admin.manage.matches.destroy', [$tournament, $match]) }}" class="mt-3" onsubmit="return confirm('Hapus pertandingan ini? Tindakan ini tidak dapat dibatalkan.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Hapus Pertandingan</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endsection
