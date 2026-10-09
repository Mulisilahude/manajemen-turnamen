@extends('layouts.admin')

@section('title', 'Edit Turnamen - Dashboard Admin')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl sm:text-3xl font-semibold">Edit Turnamen</h1>
    <p class="mt-2 text-sm text-slate-400">Perbarui informasi turnamen</p>
</div>

<div class="rounded-2xl border border-slate-800 bg-slate-900/40 p-6">
    <form method="POST" action="{{ route('admin.tournaments.update', $tournament) }}" class="space-y-6">
        @csrf
        @method('PUT')
        <div class="grid gap-6 md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="nama" class="mb-2 block text-sm text-slate-300">Nama Turnamen <span class="text-rose-400">*</span></label>
                <input id="nama" name="nama" type="text" value="{{ old('nama', $tournament->nama) }}" required class="w-full rounded-xl border border-slate-800 bg-slate-950/60 px-4 py-2.5 text-sm outline-none ring-cyan-500/40 focus:ring-2">
                @error('nama')
                    <p class="mt-2 text-sm text-rose-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="cabang_olahraga" class="mb-2 block text-sm text-slate-300">Cabang Olahraga</label>
                <input id="cabang_olahraga" name="cabang_olahraga" type="text" value="{{ old('cabang_olahraga', $tournament->cabang_olahraga) }}" class="w-full rounded-xl border border-slate-800 bg-slate-950/60 px-4 py-2.5 text-sm outline-none ring-cyan-500/40 focus:ring-2">
                @error('cabang_olahraga')
                    <p class="mt-2 text-sm text-rose-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="lokasi" class="mb-2 block text-sm text-slate-300">Lokasi</label>
                <input id="lokasi" name="lokasi" type="text" value="{{ old('lokasi', $tournament->lokasi) }}" class="w-full rounded-xl border border-slate-800 bg-slate-950/60 px-4 py-2.5 text-sm outline-none ring-cyan-500/40 focus:ring-2">
                @error('lokasi')
                    <p class="mt-2 text-sm text-rose-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="tanggal_mulai" class="mb-2 block text-sm text-slate-300">Tanggal Mulai <span class="text-rose-400">*</span></label>
                <input id="tanggal_mulai" name="tanggal_mulai" type="date" value="{{ old('tanggal_mulai', optional($tournament->tanggal_mulai)->format('Y-m-d')) }}" required class="w-full rounded-xl border border-slate-800 bg-slate-950/60 px-4 py-2.5 text-sm outline-none ring-cyan-500/40 focus:ring-2">
                @error('tanggal_mulai')
                    <p class="mt-2 text-sm text-rose-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="tanggal_selesai" class="mb-2 block text-sm text-slate-300">Tanggal Selesai <span class="text-rose-400">*</span></label>
                <input id="tanggal_selesai" name="tanggal_selesai" type="date" value="{{ old('tanggal_selesai', optional($tournament->tanggal_selesai)->format('Y-m-d')) }}" required class="w-full rounded-xl border border-slate-800 bg-slate-950/60 px-4 py-2.5 text-sm outline-none ring-cyan-500/40 focus:ring-2">
                @error('tanggal_selesai')
                    <p class="mt-2 text-sm text-rose-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="status" class="mb-2 block text-sm text-slate-300">Status <span class="text-rose-400">*</span></label>
                <select id="status" name="status" required class="w-full rounded-xl border border-slate-800 bg-slate-950/60 px-4 py-2.5 text-sm outline-none ring-cyan-500/40 focus:ring-2">
                    @php $statuses = \App\TournamentStatus::cases(); @endphp
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}" {{ old('status', $tournament->status?->value ?? $tournament->status) === $status->value ? 'selected' : '' }}>{{ $status->value }}</option>
                    @endforeach
                </select>
                @error('status')
                    <p class="mt-2 text-sm text-rose-400">{{ $message }}</p>
                @enderror
            </div>
            <div class="md:col-span-2">
                <label for="deskripsi" class="mb-2 block text-sm text-slate-300">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" rows="4" class="w-full rounded-xl border border-slate-800 bg-slate-950/60 px-4 py-2.5 text-sm outline-none ring-cyan-500/40 focus:ring-2">{{ old('deskripsi', $tournament->deskripsi) }}</textarea>
                @error('deskripsi')
                    <p class="mt-2 text-sm text-rose-400">{{ $message }}</p>
                @enderror
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="rounded-xl bg-gradient-to-r from-blue-600 to-cyan-500 px-5 py-2.5 text-sm font-medium transition hover:from-blue-500 hover:to-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-400/40">
                Simpan Perubahan
            </button>
            <a href="{{ route('admin.tournaments.index') }}" class="rounded-xl border border-slate-700 px-5 py-2.5 text-sm font-medium transition hover:border-cyan-400/60 hover:text-cyan-300">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection