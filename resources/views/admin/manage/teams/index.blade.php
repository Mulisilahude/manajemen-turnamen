@extends('layouts.manage')

@section('manage')
    <div class="page-header">
        <div>
            <h1 class="page-title">Tim</h1>
            <p class="page-subtitle">Kelola tim peserta turnamen.</p>
        </div>
    </div>

    <div class="grid gap-5 lg:grid-cols-3">
        <div class="lg:col-span-1">
            <div class="card p-5">
                <h2 class="text-lg font-semibold text-ink">Tambah Tim</h2>
                <p class="mt-1 text-xs text-muted">Daftarkan tim peserta turnamen.</p>
                <form method="POST" action="{{ route('admin.manage.teams.store', $tournament) }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="label" for="nama">Nama Tim</label>
                        <input id="nama" type="text" name="nama" value="{{ old('nama') }}" class="input" placeholder="Contoh: Garuda FC" required>
                        @error('nama')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="label" for="singkatan">Singkatan</label>
                            <input id="singkatan" type="text" name="singkatan" value="{{ old('singkatan') }}" class="input" placeholder="GRD">
                            @error('singkatan')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="label" for="pelatih">Pelatih</label>
                            <input id="pelatih" type="text" name="pelatih" value="{{ old('pelatih') }}" class="input">
                            @error('pelatih')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div>
                        <label class="label" for="status_pendaftaran">Status Pendaftaran</label>
                        <select id="status_pendaftaran" name="status_pendaftaran" class="select">
                            @foreach(\App\Enums\RegistrationStatus::cases() as $status)
                                <option value="{{ $status->value }}" @selected(old('status_pendaftaran') === $status->value)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                        @error('status_pendaftaran')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary w-full">Simpan Tim</button>
                </form>
            </div>
        </div>

        <div class="space-y-3 lg:col-span-2">
            <div class="card flex items-center justify-between p-5">
                <h2 class="text-lg font-semibold text-ink">Daftar Tim</h2>
                <span class="badge badge-primary">{{ $teams->count() }} tim</span>
            </div>

            @forelse($teams as $team)
                <div class="card">
                    <div class="flex flex-wrap items-center justify-between gap-3 p-4">
                        <div>
                            <div class="font-medium text-ink">
                                {{ $team->nama }}
                                @if($team->singkatan)
                                    <span class="ml-1 text-xs text-muted">{{ $team->singkatan }}</span>
                                @endif
                            </div>
                            <div class="text-xs text-muted">Pelatih: {{ $team->pelatih ?? '-' }} &middot; {{ $team->players_count }} pemain &middot; {{ $team->status_pendaftaran?->label() ?? $team->status_pendaftaran }}</div>
                        </div>
                        <details class="relative">
                            <summary class="btn btn-outline cursor-pointer text-xs">Kelola</summary>
                            <div class="absolute right-0 z-20 mt-2 w-72 space-y-3 rounded-xl border border-line surface p-4 shadow-lg">
                                <form method="POST" action="{{ route('admin.manage.teams.update', [$tournament, $team]) }}" class="space-y-2">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="nama" value="{{ $team->nama }}" class="input" required>
                                    <input type="text" name="singkatan" value="{{ $team->singkatan }}" class="input" placeholder="Singkatan">
                                    <input type="text" name="pelatih" value="{{ $team->pelatih }}" class="input" placeholder="Pelatih">
                                    <select name="status_pendaftaran" class="select">
                                        @foreach(\App\Enums\RegistrationStatus::cases() as $status)
                                            <option value="{{ $status->value }}" @selected($team->status_pendaftaran === $status)>{{ $status->label() }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-primary w-full !py-1.5 text-xs">Simpan Perubahan</button>
                                </form>
                                <form method="POST" action="{{ route('admin.manage.teams.destroy', [$tournament, $team]) }}" onsubmit="return confirm('Hapus tim ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger w-full text-xs">Hapus Tim</button>
                                </form>
                            </div>
                        </details>
                    </div>
                </div>
            @empty
                <div class="card border-dashed p-8 text-center text-muted">Belum ada tim. Tambahkan tim pertama melalui formulir di samping.</div>
            @endforelse
        </div>
    </div>
@endsection
