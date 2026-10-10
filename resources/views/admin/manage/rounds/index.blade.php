@extends('layouts.manage')

@section('manage')
    <div class="page-header">
        <div>
            <h1 class="page-title">Grup &amp; Putaran</h1>
            <p class="page-subtitle">Atur putaran, pembagian grup, dan lanjutan ke babak berikutnya.</p>
        </div>
    </div>

    <div class="mb-6 grid gap-5 lg:grid-cols-3">
        <div class="lg:col-span-1">
            <div class="card p-5">
                <h2 class="section-title">Buat Putaran</h2>
                <form method="POST" action="{{ route('admin.manage.rounds.store', $tournament) }}" class="mt-4 space-y-4">
                    @csrf
                    <div class="field">
                        <label class="field-label">Nama Putaran</label>
                        <input type="text" name="nama" value="{{ old('nama') }}" class="input" placeholder="Contoh: Fase Grup Awal" required>
                    </div>
                    <div class="field">
                        <label class="field-label">Tipe</label>
                        <select name="tipe" class="select">
                            <option value="grup">Fase Grup</option>
                            <option value="gugur">Fase Gugur</option>
                        </select>
                    </div>
                    <div class="field">
                        <label class="field-label">Jumlah Grup (untuk fase grup)</label>
                        <input type="number" name="jumlah_grup" value="{{ old('jumlah_grup', 2) }}" min="0" max="16" class="input">
                    </div>
                    <button type="submit" class="btn btn-primary w-full">Buat Putaran</button>
                </form>
            </div>
        </div>
        <div class="lg:col-span-2">
            <div class="card panel-grad-soft p-5 text-sm text-ink">
                <h3 class="section-title">Alur mengatur pertandingan</h3>
                <ol class="mt-2 list-decimal space-y-1 pl-5 text-muted">
                    <li>Buat putaran fase grup beserta jumlah grup.</li>
                    <li>Isi setiap grup dengan tim peserta.</li>
                    <li>Jadwalkan pertandingan pada menu Pertandingan.</li>
                    <li>Setelah semua pertandingan grup selesai, gunakan tombol "Buat lanjutan" untuk membuat grup lanjutan atau babak gugur dari tim yang lolos.</li>
                    <li>Pemenang final otomatis menjadi juara turnamen.</li>
                </ol>
            </div>
        </div>
    </div>

    @forelse($tournament->rounds as $round)
        <div class="card mb-5 overflow-hidden">
            <div class="card-header">
                <div>
                    <h3 class="section-title">Putaran {{ $round->nomor }}: {{ $round->nama }}</h3>
                    <p class="text-xs text-muted">{{ $round->tipe?->label() }} &middot; {{ $round->matches->count() }} pertandingan</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @if($round->isGrup())
                        <a href="{{ route('admin.manage.groups.kelola', [$tournament, $round]) }}" class="btn btn-outline !px-3 !py-1.5 text-xs">Atur Grup</a>
                        <a href="{{ route('admin.manage.groups.undian', [$tournament, $round]) }}" class="btn btn-outline !px-3 !py-1.5 text-xs">Undian</a>
                        <a href="{{ route('admin.manage.jadwal.index', [$tournament, $round]) }}" class="btn btn-outline !px-3 !py-1.5 text-xs">Jadwal Otomatis</a>
                    @endif
                    @if(! $round->lanjutan_dibuat && $round->matches->isNotEmpty())
                        <a href="{{ route('admin.manage.rounds.lanjut', [$tournament, $round]) }}" class="btn btn-primary !px-3 !py-1.5 text-xs">Buat lanjutan</a>
                    @endif
                    <form method="POST" action="{{ route('admin.manage.rounds.destroy', [$tournament, $round]) }}" onsubmit="return confirm('Hapus putaran ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger !px-3 !py-1.5 text-xs">Hapus</button>
                    </form>
                </div>
            </div>

            @if($round->isGrup())
                <div class="space-y-4 p-5">
                    @foreach($round->groups as $group)
                        @php $klasemen = app(\App\Services\KlasemenService::class)->klasemenGrup($group); @endphp
                        <div class="rounded-xl border border-line">
                            <div class="flex items-center justify-between border-b border-line px-4 py-2">
                                <span class="font-medium text-ink">{{ $group->nama }}</span>
                                <span class="badge badge-muted">{{ $group->teams->count() }} tim</span>
                            </div>
                            <div class="grid gap-4 p-4 md:grid-cols-2">
                                <form method="POST" action="{{ route('admin.manage.groups.isi', [$tournament, $group]) }}" class="space-y-2">
                                    @csrf
                                    <p class="text-xs font-medium text-muted">Pilih peserta grup:</p>
                                    <div class="max-h-48 space-y-0.5 overflow-y-auto rounded-lg border border-line p-2">
                                        @forelse($teams as $team)
                                            <label class="flex items-center gap-2 py-1 text-sm text-ink">
                                                <input type="checkbox" name="team_ids[]" value="{{ $team->id }}" @checked($group->teams->contains($team->id)) class="rounded border-line">
                                                {{ $team->nama }}
                                            </label>
                                        @empty
                                            <p class="text-xs text-muted">Belum ada tim.</p>
                                        @endforelse
                                    </div>
                                    <button type="submit" class="btn btn-outline !px-3 !py-1.5 text-xs">Simpan Anggota</button>
                                </form>

                                <div class="overflow-x-auto">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th class="py-1 text-left">#</th>
                                                <th class="py-1 text-left">Tim</th>
                                                <th class="py-1 text-center">M</th>
                                                <th class="py-1 text-center">SG</th>
                                                <th class="py-1 text-center">Poin</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($klasemen as $row)
                                                <tr>
                                                    <td class="py-1"><span class="rank-marker {{ $row['peringkat'] <= 2 ? 'rank-marker-lolos' : '' }}">{{ $row['peringkat'] }}</span></td>
                                                    <td class="py-1 font-medium text-ink">{{ $row['nama'] }}</td>
                                                    <td class="py-1 text-center text-muted">{{ $row['main'] }}</td>
                                                    <td class="py-1 text-center text-muted">{{ $row['sg'] }}</td>
                                                    <td class="py-1 text-center font-semibold text-ink">{{ $row['poin'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <form method="POST" action="{{ route('admin.manage.groups.store', [$tournament, $round]) }}" class="flex items-end gap-2">
                        @csrf
                        <div class="field">
                            <label class="field-label">Tambah grup</label>
                            <input type="text" name="nama" class="input" placeholder="Grup C" required>
                        </div>
                        <button type="submit" class="btn btn-outline text-xs">Tambah</button>
                    </form>
                </div>
            @else
                <div class="divide-y divide-[color:var(--line)]">
                    @forelse($round->matches as $match)
                        <div class="flex items-center justify-between gap-3 px-5 py-3 text-sm">
                            <span class="text-ink">{{ $match->teamA?->nama ?? 'TBD' }} <span class="text-muted">vs</span> {{ $match->teamB?->nama ?? 'TBD' }}</span>
                            <span class="font-display font-semibold text-ink">{{ $match->skorLabel() }}</span>
                        </div>
                    @empty
                        <div class="px-5 py-4 text-sm text-muted">Belum ada pertandingan pada babak ini.</div>
                    @endforelse
                </div>
            @endif
        </div>
    @empty
        <div class="empty-state">
            <span class="empty-state-icon">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            </span>
            <p class="font-medium text-ink">Belum ada putaran</p>
            <p>Buat putaran pertama melalui formulir di samping.</p>
        </div>
    @endforelse
@endsection
