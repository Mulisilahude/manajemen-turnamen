@extends('layouts.manage')

@section('manage')
    @php
        $hariLabel = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
    @endphp

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-ink">Penjadwalan Otomatis</h2>
            <p class="mt-1 text-sm text-muted">Putaran {{ $round->nomor }}: {{ $round->nama }} &middot; {{ $round->groups->count() }} grup</p>
        </div>
        <a href="{{ route('admin.manage.matches.index', $tournament) }}" class="btn btn-outline">Daftar Pertandingan</a>
    </div>

    @if($sudahAdaJadwal)
        <div class="alert alert-warning mb-4">
            Putaran ini memiliki pertandingan yang sedang berlangsung atau selesai. Jadwal tidak dapat dibuat ulang.
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-1">
            <div class="card p-5">
                <h3 class="font-semibold text-ink">Konfigurasi Jadwal</h3>
                <form method="POST" action="{{ route('admin.manage.jadwal.proses', [$tournament, $round]) }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label for="tanggal_mulai" class="label">Tanggal Mulai</label>
                        <input id="tanggal_mulai" name="tanggal_mulai" type="date" value="{{ old('tanggal_mulai', $config['tanggal_mulai']) }}" required class="input">
                        @error('tanggal_mulai')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="tanggal_selesai" class="label">Tanggal Selesai</label>
                        <input id="tanggal_selesai" name="tanggal_selesai" type="date" value="{{ old('tanggal_selesai', $config['tanggal_selesai']) }}" class="input">
                        @error('tanggal_selesai')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="jam_mulai" class="label">Jam Mulai</label>
                            <input id="jam_mulai" name="jam_mulai" type="time" value="{{ old('jam_mulai', $config['jam_mulai']) }}" required class="input">
                            @error('jam_mulai')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="jam_selesai" class="label">Jam Selesai</label>
                            <input id="jam_selesai" name="jam_selesai" type="time" value="{{ old('jam_selesai', $config['jam_selesai']) }}" required class="input">
                            @error('jam_selesai')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="durasi" class="label">Durasi (menit)</label>
                            <input id="durasi" name="durasi" type="number" min="5" max="240" value="{{ old('durasi', $config['durasi']) }}" required class="input">
                            @error('durasi')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="jeda" class="label">Jeda (menit)</label>
                            <input id="jeda" name="jeda" type="number" min="0" max="120" value="{{ old('jeda', $config['jeda']) }}" class="input">
                            @error('jeda')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div>
                        <label for="lapangan" class="label">Lapangan (satu per baris)</label>
                        <textarea id="lapangan" name="lapangan" rows="3" placeholder="Lapangan A&#10;Lapangan B" class="textarea">{{ old('lapangan', implode("\n", (array) $config['lapangan'])) }}</textarea>
                        @error('lapangan')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <span class="label">Hari Pertandingan</span>
                        <div class="mt-1 grid grid-cols-2 gap-2">
                            @foreach($hariLabel as $num => $label)
                                <label class="flex items-center gap-2 text-sm text-muted">
                                    <input type="checkbox" name="hari[]" value="{{ $num }}" @checked(in_array($num, (array) old('hari', $config['hari']), true)) class="rounded border-line">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-1 text-xs text-muted">Kosongkan untuk memakai semua hari.</p>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-muted">
                        <input type="checkbox" name="dua_putaran" value="1" @checked(old('dua_putaran', $config['dua_putaran'])) class="rounded border-line">
                        Dua putaran (tandang &amp; kandang)
                    </label>
                    <button type="submit" @disabled($sudahAdaJadwal) class="btn btn-primary w-full disabled:opacity-50">Buat Pratinjau</button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-2">
            @if($draft)
                <div class="card overflow-hidden">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line px-5 py-4">
                        <div>
                            <h3 class="font-semibold text-ink">Pratinjau Jadwal</h3>
                            <p class="text-xs text-muted">{{ count($draft['rows']) }} pertandingan &middot; belum disimpan</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('admin.manage.jadwal.sahkan', [$tournament, $round]) }}">
                                @csrf
                                <button type="submit" @disabled(! empty($draft['errors']) || $sudahAdaJadwal) class="btn btn-primary disabled:opacity-50">Sahkan Jadwal</button>
                            </form>
                            <form method="POST" action="{{ route('admin.manage.jadwal.batal', [$tournament, $round]) }}">
                                @csrf
                                <button type="submit" class="btn btn-danger">Batal</button>
                            </form>
                        </div>
                    </div>

                    @if(! empty($draft['errors']))
                        <div class="alert alert-error m-5">
                            <p class="font-medium">Ada bentrok atau kekurangan slot yang perlu diperbaiki:</p>
                            <ul class="mt-1 list-disc space-y-0.5 pl-5">
                                @foreach($draft['errors'] as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-surface-2 text-left text-xs uppercase tracking-wide text-muted">
                                <tr>
                                    <th class="px-4 py-3">#</th>
                                    <th class="px-4 py-2">Pertandingan</th>
                                    <th class="px-4 py-2">Tanggal</th>
                                    <th class="px-4 py-2">Jam</th>
                                    <th class="px-4 py-2">Lapangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[color:var(--line)]">
                                @foreach($draft['rows'] as $i => $row)
                                    <tr>
                                        <td class="px-4 py-2 text-muted">{{ $i + 1 }}</td>                                        <td class="px-4 py-2 text-ink">
                                            {{ $namaTim[$row['tim_a_id']] ?? ('Tim #'.$row['tim_a_id']) }}
                                            vs
                                            {{ $namaTim[$row['tim_b_id']] ?? ('Tim #'.$row['tim_b_id']) }}
                                        </td>
                                        <td class="px-4 py-2 text-muted">{{ $row['tanggal'] ?? '-' }}</td>
                                        <td class="px-4 py-2 text-muted">{{ $row['jam'] ?? '-' }}</td>
                                        <td class="px-4 py-2 text-muted">{{ $row['lokasi'] ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="card border-dashed p-8 text-center text-muted">
                    Belum ada pratinjau jadwal. Isi konfigurasi lalu tekan "Buat Pratinjau".
                </div>
            @endif
        </div>
    </div>
@endsection
