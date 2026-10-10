@extends('layouts.manage')

@section('manage')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-ink">Impor Data Resmi Panitia</h2>
            <p class="mt-1 text-sm text-muted">
                Tempel pembagian grup dan jadwal resmi. Sistem <span class="font-medium text-ink">tidak mengundi</span> maupun
                <span class="font-medium text-ink">menjadwalkan otomatis</span> &mdash; data disimpan apa adanya lalu diperiksa.
            </p>
        </div>
        <a href="{{ route('admin.manage.matches.index', $tournament) }}" class="btn btn-outline">Daftar Pertandingan</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error mb-4">{{ session('error') }}</div>
    @endif
    @error('impor')
        <div class="alert alert-error mb-4">{{ $message }}</div>
    @enderror

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Formulir --}}
        <div class="lg:col-span-1 space-y-6">
            <form method="POST" action="{{ route('admin.manage.impor.pratinjau', $tournament) }}" class="card p-5">
                @csrf
                <h3 class="font-semibold text-ink">Teks Sumber</h3>
                <p class="mt-1 text-xs text-muted">Format grup: <code>Grup A</code> lalu satu tim per baris. Format jadwal: <code>Tanggal | Jam | Kode | Tim A | Tim B | Grup | Skor</code>.</p>

                <div class="mt-4">
                    <label for="teks_grup" class="label">Pembagian Grup</label>
                    <textarea id="teks_grup" name="teks_grup" rows="10" class="textarea font-mono text-xs" placeholder="Grup A&#10;Tim Satu&#10;Tim Dua">{{ old('teks_grup', $teksGrup) }}</textarea>
                </div>
                <div class="mt-4">
                    <label for="teks_jadwal" class="label">Jadwal Pertandingan</label>
                    <textarea id="teks_jadwal" name="teks_jadwal" rows="14" class="textarea font-mono text-xs" placeholder="2026-10-10 | 20:15 | | Tim A | Tim B | A |">{{ old('teks_jadwal', $teksJadwal) }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary mt-4 w-full">Buat Pratinjau</button>

                @if($pratinjau)
                    <button type="submit" form="impor-reset" class="btn btn-outline mt-2 w-full">Bersihkan Pratinjau</button>
                @endif
            </form>

            <div class="card p-5">
                <h3 class="font-semibold text-ink">Data Tersimpan Saat Ini</h3>
                <dl class="mt-3 grid grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-muted">Tim</dt><dd class="text-lg font-semibold text-ink">{{ $tersimpan['tim'] }}</dd></div>
                    <div><dt class="text-muted">Grup</dt><dd class="text-lg font-semibold text-ink">{{ $tersimpan['grup'] }}</dd></div>
                    <div><dt class="text-muted">Pertandingan</dt><dd class="text-lg font-semibold text-ink">{{ $tersimpan['pertandingan'] }}</dd></div>
                    <div><dt class="text-muted">Status Publik</dt><dd class="text-lg font-semibold {{ $tersimpan['publik'] ? 'text-emerald-600' : 'text-amber-600' }}">{{ $tersimpan['publik'] ? 'Tampil' : 'Draf' }}</dd></div>
                </dl>
                @if($tersimpan['rentang'][0])
                    <p class="mt-3 text-xs text-muted">Rentang tanggal: {{ $tersimpan['rentang'][0] }} s/d {{ $tersimpan['rentang'][1] }}</p>
                @endif

                @if(count($tersimpan['belum_pemain']) > 0)
                    <div class="mt-4 rounded-xl border border-amber-300 bg-amber-50 p-3 text-xs text-amber-800">
                        <p class="font-medium">Detail pemain belum lengkap pada {{ count($tersimpan['belum_pemain']) }} tim.</p>
                        <p class="mt-1">Skor tetap dapat dicatat; rincian pemain dapat dilengkapi kemudian tanpa mengubah skor.</p>
                        <p class="mt-2 leading-relaxed">{{ implode(', ', array_slice($tersimpan['belum_pemain'], 0, 12)) }}{{ count($tersimpan['belum_pemain']) > 12 ? ', …' : '' }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Pratinjau --}}
        <div class="lg:col-span-2 space-y-6">
            @if(! $pratinjau)
                <div class="card border-dashed p-8 text-center text-muted">
                    Belum ada pratinjau. Tempel data resmi lalu tekan "Buat Pratinjau".
                </div>
            @else
                @php
                    $rekap = $pratinjau['rekap'];
                    $kesalahan = collect($pratinjau['masalah'])->where('tingkat', 'kesalahan');
                    $peringatan = collect($pratinjau['masalah'])->where('tingkat', 'peringatan');
                    $jadwalUrut = collect($pratinjau['jadwal'])->sortBy(fn ($r) => ($r['tanggal'] ?? '9999').' '.($r['jam'] ?? '99:99'))->values();
                @endphp

                <div class="card p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h3 class="font-semibold text-ink">Ringkasan Pratinjau</h3>
                        <div class="flex flex-wrap gap-2 text-xs">
                            <span class="badge">{{ $rekap['jumlah_grup'] }} grup</span>
                            <span class="badge">{{ $rekap['jumlah_tim_unik'] }} tim unik</span>
                            <span class="badge">{{ $rekap['jumlah_pertandingan'] }} pertandingan</span>
                        </div>
                    </div>
                    @if($rekap['rentang_tanggal'][0])
                        <p class="mt-2 text-xs text-muted">Rentang tanggal: {{ $rekap['rentang_tanggal'][0] }} s/d {{ $rekap['rentang_tanggal'][1] }}</p>
                    @endif

                    <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-xl border border-red-200 bg-red-50 p-3">
                            <p class="text-xs font-medium text-red-700">Kesalahan (harus diperbaiki)</p>
                            <p class="text-2xl font-semibold text-red-700">{{ $kesalahan->count() }}</p>
                        </div>
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-3">
                            <p class="text-xs font-medium text-amber-700">Perlu konfirmasi</p>
                            <p class="text-2xl font-semibold text-amber-700">{{ $peringatan->count() + count($rekap['perlu_konfirmasi']) }}</p>
                        </div>
                    </div>
                </div>

                @if($kesalahan->isNotEmpty() || $peringatan->isNotEmpty())
                    <div class="card p-5">
                        <h3 class="font-semibold text-ink">Daftar Masalah</h3>
                        <ul class="mt-3 space-y-2 text-sm">
                            @foreach($kesalahan as $m)
                                <li class="flex gap-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-red-800">
                                    <span class="font-semibold">!</span><span>{{ $m['pesan'] }}</span>
                                </li>
                            @endforeach
                            @foreach($peringatan as $m)
                                <li class="flex gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-amber-800">
                                    <span aria-hidden="true">&middot;</span><span>{{ $m['pesan'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="card overflow-hidden">
                    <div class="border-b border-line px-5 py-4">
                        <h3 class="font-semibold text-ink">Pratinjau Pembagian Grup</h3>
                    </div>
                    <div class="grid gap-px bg-line sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($pratinjau['grup'] as $g)
                            <div class="bg-surface p-4">
                                <p class="text-sm font-semibold text-ink">{{ $g['nama'] }}</p>
                                <ol class="mt-2 space-y-1 text-sm text-muted">
                                    @foreach($g['tim'] as $tim)
                                        <li>{{ $loop->iteration }}. {{ $tim }}</li>
                                    @endforeach
                                </ol>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="card overflow-hidden">
                    <div class="border-b border-line px-5 py-4">
                        <h3 class="font-semibold text-ink">Pratinjau Jadwal ({{ $jadwalUrut->count() }} pertandingan)</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-surface-2 text-left text-xs uppercase tracking-wide text-muted">
                                <tr>
                                    <th class="px-4 py-3">#</th>
                                    <th class="px-4 py-2">Tanggal</th>
                                    <th class="px-4 py-2">Jam</th>
                                    <th class="px-4 py-2">Grup</th>
                                    <th class="px-4 py-2">Kode</th>
                                    <th class="px-4 py-2">Pertandingan</th>
                                    <th class="px-4 py-2">Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[color:var(--line)]">
                                @foreach($jadwalUrut as $i => $row)
                                    <tr class="{{ ! empty($row['perlu_konfirmasi']) ? 'bg-amber-50/60' : '' }}">
                                        <td class="px-4 py-2 text-muted">{{ $i + 1 }}</td>
                                        <td class="px-4 py-2 text-muted">{{ $row['tanggal'] ?? ($row['tanggal_teks'] ?? '-') }}</td>
                                        <td class="px-4 py-2 text-muted">{{ $row['jam'] ?? ($row['jam_teks'] ?? '-') }}</td>
                                        <td class="px-4 py-2 text-muted">{{ $row['grup'] ?? '-' }}</td>
                                        <td class="px-4 py-2 text-muted">{{ $row['kode'] ?? '-' }}</td>
                                        <td class="px-4 py-2 text-ink">{{ $row['tim_a'] }} <span class="text-muted">vs</span> {{ $row['tim_b'] }}</td>
                                        <td class="px-4 py-2 text-xs text-amber-700">{{ implode(', ', $row['perlu_konfirmasi']) ?: '' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card p-5">
                    <h3 class="font-semibold text-ink">Simpan</h3>
                    <p class="mt-1 text-sm text-muted">
                        <span class="font-medium text-ink">Simpan sebagai Draf</span> menyimpan tanpa menampilkan ke publik.
                        <span class="font-medium text-ink">Sahkan &amp; Tampilkan</span> menyimpan dan langsung menampilkan ke halaman publik.
                        Keduanya aman diklik berulang dan tidak menimpa pertandingan yang sudah punya skor.
                    </p>
                    @if($kesalahan->isNotEmpty())
                        <div class="alert alert-error mt-3">Perbaiki {{ $kesalahan->count() }} kesalahan terlebih dahulu sebelum menyimpan.</div>
                    @endif
                    <form method="POST" action="{{ route('admin.manage.impor.simpan', $tournament) }}" class="mt-4 flex flex-wrap gap-3">
                        @csrf
                        <input type="hidden" name="teks_grup" value="{{ $teksGrup }}">
                        <input type="hidden" name="teks_jadwal" value="{{ $teksJadwal }}">
                        <button type="submit" name="mode" value="draf" @disabled($kesalahan->isNotEmpty()) class="btn btn-outline disabled:opacity-50">Simpan sebagai Draf</button>
                        <button type="submit" name="mode" value="sahkan" @disabled($kesalahan->isNotEmpty()) class="btn btn-primary disabled:opacity-50">Sahkan &amp; Tampilkan ke Publik</button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    <form id="impor-reset" method="POST" action="{{ route('admin.manage.impor.reset', $tournament) }}" class="hidden">
        @csrf
    </form>
@endsection
