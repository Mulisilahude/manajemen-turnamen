@extends('layouts.manage')

@section('manage')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-ink">Undian Otomatis</h2>
            <p class="mt-1 text-sm text-muted">Putaran {{ $round->nomor }}: {{ $round->nama }} &middot; {{ $teams->count() }} tim tersedia</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.manage.groups.kelola', [$tournament, $round]) }}" class="btn btn-outline">Atur Manual</a>
            <a href="{{ route('admin.manage.rounds.index', $tournament) }}" class="btn btn-ghost">Kembali</a>
        </div>
    </div>

    @if($sudahDijadwalkan)
        <div class="alert alert-warning mb-4">
            Undian tidak dapat disahkan karena putaran ini sudah memiliki jadwal atau hasil pertandingan.
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-1">
            <div class="card p-5">
                <h3 class="font-semibold text-ink">Buat Undian</h3>
                <p class="mt-1 text-xs text-muted">Tim akan dibagi secara acak ke dalam sejumlah grup dengan jumlah tim yang merata.</p>
                <form method="POST" action="{{ route('admin.manage.groups.undian.proses', [$tournament, $round]) }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label for="jumlah_grup" class="label">Jumlah Grup</label>
                        <input id="jumlah_grup" name="jumlah_grup" type="number" min="2" max="16" value="{{ old('jumlah_grup', $draft['jumlah_grup'] ?? 2) }}" class="input">
                        @error('jumlah_grup')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary w-full">Acak &amp; Pratinjau</button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-2">
            @if($draft)
                <div class="card p-5">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="font-semibold text-ink">Pratinjau Undian</h3>
                            <p class="text-xs text-muted">{{ count($draft['groups']) }} grup &middot; belum disimpan</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('admin.manage.groups.undian.sahkan', [$tournament, $round]) }}">
                                @csrf
                                <button type="submit" @disabled($sudahDijadwalkan) class="btn btn-primary disabled:opacity-50">Sahkan Undian</button>
                            </form>
                            <form method="POST" action="{{ route('admin.manage.groups.undian.batal', [$tournament, $round]) }}">
                                @csrf
                                <button type="submit" class="btn btn-outline">Batal</button>
                            </form>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach($draft['groups'] as $grup)
                            <div class="rounded-xl border border-line bg-surface-2 p-4">
                                <p class="text-sm font-semibold text-ink">{{ $grup['nama'] }}</p>
                                <ul class="mt-2 space-y-1 text-sm text-muted">
                                    @foreach($grup['tim_ids'] as $timId)
                                        <li>{{ $teams->firstWhere('id', $timId)?->nama ?? ('Tim #'.$timId) }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="card border-dashed p-8 text-center text-muted">
                    Belum ada pratinjau undian. Tentukan jumlah grup lalu tekan "Acak &amp; Pratinjau".
                </div>
            @endif
        </div>
    </div>
@endsection
