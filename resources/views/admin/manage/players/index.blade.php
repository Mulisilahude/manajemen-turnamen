@extends('layouts.manage')

@section('manage')
    <div class="page-header">
        <div>
            <h1 class="page-title">Pemain</h1>
            <p class="page-subtitle">Data pemain per tim untuk turnamen ini.</p>
        </div>
    </div>

    <div class="grid gap-5 lg:grid-cols-3">
        <div class="lg:col-span-1">
            <div class="card p-5">
                <h2 class="section-title">Tambah Pemain</h2>
                @if($teams->isEmpty())
                    <p class="mt-3 text-sm text-muted">Tambahkan tim terlebih dahulu sebelum mendata pemain.</p>
                @else
                    <form method="POST" action="{{ route('admin.manage.players.store', $tournament) }}" class="mt-4 space-y-4">
                        @csrf
                        <div class="field">
                            <label class="field-label">Tim</label>
                            <select name="team_id" class="select" required>
                                @foreach($teams as $team)
                                    <option value="{{ $team->id }}" @selected(old('team_id') == $team->id)>{{ $team->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label class="field-label">Nama Pemain</label>
                            <input type="text" name="nama" value="{{ old('nama') }}" class="input" required>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="field">
                                <label class="field-label">Nomor</label>
                                <input type="number" name="nomor_punggung" value="{{ old('nomor_punggung') }}" class="input">
                            </div>
                            <div class="field">
                                <label class="field-label">Posisi</label>
                                <input type="text" name="posisi" value="{{ old('posisi') }}" class="input">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-full">Simpan Pemain</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="space-y-4 lg:col-span-2">
            @forelse($teams as $team)
                <div class="card overflow-hidden">
                    <div class="card-header !py-3">
                        <h3 class="font-medium text-ink">{{ $team->nama }}</h3>
                        <span class="badge badge-muted">{{ $team->players->count() }} pemain</span>
                    </div>
                    <div class="divide-y divide-[color:var(--line)]">
                        @forelse($team->players as $player)
                            <div class="flex items-center justify-between gap-3 px-4 py-2.5 text-sm">
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-[color:color-mix(in_srgb,var(--primary)_14%,transparent)] text-xs font-semibold text-primary">{{ $player->nomor_punggung ?? '-' }}</span>
                                    <span class="truncate text-ink">{{ $player->nama }}</span>
                                    @if($player->posisi)<span class="text-xs text-muted">{{ $player->posisi }}</span>@endif
                                </div>
                                <details class="relative">
                                    <summary class="btn btn-outline cursor-pointer !px-2.5 !py-1 text-xs">Ubah</summary>
                                    <div class="absolute right-0 z-20 mt-2 w-64 space-y-2 rounded-xl border border-line bg-surface p-4 shadow-lg">
                                        <form method="POST" action="{{ route('admin.manage.players.update', [$tournament, $player]) }}" class="space-y-2">
                                            @csrf
                                            @method('PUT')
                                            <select name="team_id" class="select">
                                                @foreach($teams as $t)
                                                    <option value="{{ $t->id }}" @selected($player->team_id === $t->id)>{{ $t->nama }}</option>
                                                @endforeach
                                            </select>
                                            <input type="text" name="nama" value="{{ $player->nama }}" class="input" required>
                                            <input type="number" name="nomor_punggung" value="{{ $player->nomor_punggung }}" class="input" placeholder="Nomor">
                                            <input type="text" name="posisi" value="{{ $player->posisi }}" class="input" placeholder="Posisi">
                                            <button type="submit" class="btn btn-primary w-full !py-1.5 text-xs">Simpan</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.manage.players.destroy', [$tournament, $player]) }}" onsubmit="return confirm('Hapus pemain ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger w-full text-xs">Hapus</button>
                                        </form>
                                    </div>
                                </details>
                            </div>
                        @empty
                            <div class="px-4 py-4 text-sm text-muted">Belum ada pemain.</div>
                        @endforelse
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <p class="font-medium text-ink">Belum ada tim</p>
                    <p>Tambahkan tim terlebih dahulu untuk mendata pemain.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
