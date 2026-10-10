@extends('layouts.manage')

@section('manage')
    <div class="page-header">
        <div>
            <h1 class="page-title">Wasit</h1>
            <p class="page-subtitle">Daftar wasit yang bertugas pada turnamen ini.</p>
        </div>
    </div>

    <div class="grid gap-5 lg:grid-cols-3">
        <div class="lg:col-span-1">
            <div class="card p-5">
                <h2 class="section-title">Tambah Wasit</h2>
                <form method="POST" action="{{ route('admin.manage.referees.store', $tournament) }}" class="mt-4 space-y-4">
                    @csrf
                    <div class="field">
                        <label class="field-label">Nama Wasit</label>
                        <input type="text" name="nama" value="{{ old('nama') }}" class="input" required>
                    </div>
                    <div class="field">
                        <label class="field-label">Nomor Kontak</label>
                        <input type="text" name="nomor_kontak" value="{{ old('nomor_kontak') }}" class="input">
                    </div>
                    <button type="submit" class="btn btn-primary w-full">Simpan Wasit</button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="card overflow-hidden">
                <div class="card-header">
                    <h2 class="section-title">Daftar Wasit</h2>
                    <span class="badge badge-muted">{{ $referees->count() }} wasit</span>
                </div>
                <div class="divide-y divide-[color:var(--line)]">
                    @forelse($referees as $referee)
                        <div class="flex items-center justify-between gap-3 px-5 py-3 text-sm">
                            <div>
                                <div class="font-medium text-ink">{{ $referee->nama }}</div>
                                <div class="text-xs text-muted">{{ $referee->nomor_kontak ?? '-' }} &middot; {{ $referee->matches_count }} pertandingan</div>
                            </div>
                            <details class="relative">
                                <summary class="btn btn-outline cursor-pointer !px-2.5 !py-1 text-xs">Ubah</summary>
                                <div class="absolute right-0 z-20 mt-2 w-64 space-y-2 rounded-xl border border-line bg-surface p-4 shadow-lg">
                                    <form method="POST" action="{{ route('admin.manage.referees.update', [$tournament, $referee]) }}" class="space-y-2">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="nama" value="{{ $referee->nama }}" class="input" required>
                                        <input type="text" name="nomor_kontak" value="{{ $referee->nomor_kontak }}" class="input" placeholder="Kontak">
                                        <button type="submit" class="btn btn-primary w-full !py-1.5 text-xs">Simpan</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.manage.referees.destroy', [$tournament, $referee]) }}" onsubmit="return confirm('Hapus wasit ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger w-full text-xs">Hapus</button>
                                    </form>
                                </div>
                            </details>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center text-muted">Belum ada wasit.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
