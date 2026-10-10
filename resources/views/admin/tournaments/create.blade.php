@extends('layouts.admin')

@section('title', 'Tambah Turnamen - Dashboard Admin')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Tambah Turnamen Baru</h1>
            <p class="page-subtitle">Isi informasi dasar turnamen.</p>
        </div>
        <a href="{{ route('admin.tournaments.index') }}" class="btn btn-outline">Kembali</a>
    </div>

    <div class="card p-6">
        <form method="POST" action="{{ route('admin.tournaments.store') }}" class="space-y-6">
            @csrf
            <div class="fieldset fieldset-2">
                <div class="field sm:col-span-2">
                    <label for="nama" class="field-label">Nama Turnamen <span class="text-red-600">*</span></label>
                    <input id="nama" name="nama" type="text" value="{{ old('nama') }}" required class="input" placeholder="Contoh: Brimob Cup 2026">
                    @error('nama')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="cabang_olahraga" class="field-label">Cabang Olahraga</label>
                    <input id="cabang_olahraga" name="cabang_olahraga" type="text" value="{{ old('cabang_olahraga') }}" placeholder="Contoh: Futsal, Basket, Voli" class="input">
                    @error('cabang_olahraga')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="lokasi" class="field-label">Lokasi</label>
                    <input id="lokasi" name="lokasi" type="text" value="{{ old('lokasi') }}" class="input" placeholder="Contoh: Gor Manado">
                    @error('lokasi')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="tanggal_mulai" class="field-label">Tanggal Mulai <span class="text-red-600">*</span></label>
                    <input id="tanggal_mulai" name="tanggal_mulai" type="date" value="{{ old('tanggal_mulai') }}" required class="input">
                    @error('tanggal_mulai')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="tanggal_selesai" class="field-label">Tanggal Selesai <span class="text-red-600">*</span></label>
                    <input id="tanggal_selesai" name="tanggal_selesai" type="date" value="{{ old('tanggal_selesai') }}" required class="input">
                    @error('tanggal_selesai')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="status" class="field-label">Status <span class="text-red-600">*</span></label>
                    <select id="status" name="status" required class="select">
                        @foreach(\App\TournamentStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected(old('status') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                    @error('status')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field sm:col-span-2">
                    <label for="deskripsi" class="field-label">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" rows="4" class="textarea" placeholder="Deskripsi singkat turnamen (opsional)">{{ old('deskripsi') }}</textarea>
                    @error('deskripsi')<p class="field-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3 border-t border-line pt-5">
                <button type="submit" class="btn btn-primary">Simpan Turnamen</button>
                <a href="{{ route('admin.tournaments.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
@endsection
