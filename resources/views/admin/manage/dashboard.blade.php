@extends('layouts.manage')

@section('manage')
    @php
        $ikon = fn (string $d) => '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="' . $d . '" /></svg>';

        $kartu = [
            ['label' => 'Tim Terdaftar', 'nilai' => $ringkasan['tim'], 'icon' => $ikon('M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z')],
            ['label' => 'Pemain', 'nilai' => $ringkasan['pemain'], 'icon' => $ikon('M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z')],
            ['label' => 'Wasit', 'nilai' => $ringkasan['wasit'], 'icon' => $ikon('M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z')],
            ['label' => 'Putaran', 'nilai' => $ringkasan['putaran'], 'icon' => $ikon('M6 6.878V6a2.25 2.25 0 0 1 2.25-2.25h7.5A2.25 2.25 0 0 1 18 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 0 0 4.5 9v.878m13.5-3A2.25 2.25 0 0 1 19.5 9v.878m0 0a2.246 2.246 0 0 0-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0 1 21 12v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6c0-.98.626-1.813 1.5-2.122')],
            ['label' => 'Pertandingan', 'nilai' => $ringkasan['pertandingan'], 'icon' => $ikon('M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5')],
            ['label' => 'Selesai', 'nilai' => $ringkasan['selesai'], 'icon' => $ikon('M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z')],
            ['label' => 'Total Gol', 'nilai' => $ringkasan['gol'], 'icon' => $ikon('M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25')],
            ['label' => 'Juara', 'nilai' => null],
        ];
    @endphp

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
        @foreach($kartu as $item)
            @if($item['label'] === 'Juara')
                <div class="relative overflow-hidden rounded-2xl p-5 text-white shadow-sm panel-grad">
                    <div class="pitch-pattern absolute inset-0 opacity-30"></div>
                    <div class="relative">
                        <p class="text-xs font-medium uppercase tracking-wide text-white/80">Juara</p>
                        <p class="font-display mt-2 text-lg font-semibold leading-snug">{{ $juara?->nama ?? 'Belum ditentukan' }}</p>
                    </div>
                </div>
            @else
                <div class="stat-card">
                    <div class="flex items-start justify-between gap-3">
                        <span class="stat-icon">{!! $item['icon'] !!}</span>
                    </div>
                    <p class="stat-value font-display mt-3 text-3xl text-ink">{{ $item['nilai'] }}</p>
                    <p class="stat-label mt-1">{{ $item['label'] }}</p>
                </div>
            @endif
        @endforeach
    </div>

    <div class="grid gap-5 lg:grid-cols-3">
        <div class="lg:col-span-2 card overflow-hidden">
            <div class="card-header">
                <h2 class="section-title">Pertandingan Terbaru</h2>
                <a href="{{ route('admin.manage.matches.index', $tournament) }}" class="text-sm text-primary transition hover:opacity-80">Lihat semua</a>
            </div>
            <div class="table-wrap" style="border:0;border-radius:0;box-shadow:none">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 text-left">Babak</th>
                            <th class="px-5 py-3 text-left">Pertandingan</th>
                            <th class="px-5 py-3 text-center">Skor</th>
                            <th class="px-5 py-3 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pertandinganTerbaru as $match)
                            <tr>
                                <td class="px-5 py-3 text-muted">{{ $match->babakLabel() }}</td>
                                <td class="px-5 py-3">
                                    <div class="font-medium text-ink">{{ $match->teamA?->nama ?? 'TBD' }} vs {{ $match->teamB?->nama ?? 'TBD' }}</div>
                                    <div class="text-xs text-muted">
                                        {{ $match->group?->nama }}
                                        @if($match->tanggal)&middot; {{ $match->tanggal->format('d M Y') }}@endif
                                        @if($match->jam)&middot; {{ $match->jam }}@endif
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="score-pill font-display">{{ $match->skorLabel() }}</span>
                                </td>
                                <td class="px-5 py-3">
                                    @php $st = $match->status?->value ?? $match->status; @endphp
                                    <span class="badge {{ $st === 'selesai' ? 'badge-success' : ($st === 'berlangsung' ? 'badge-primary' : 'badge-muted') }}">
                                        {{ $match->status?->label() ?? $match->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-10 text-center text-muted">Belum ada pertandingan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-5">
            <div class="card p-5">
                <h2 class="section-title">Pertandingan Berikutnya</h2>
                @if($pertandinganBerikutnya)
                    <div class="mt-3 rounded-xl border border-line bg-surface-2 p-4">
                        <p class="text-xs text-muted">
                            {{ $pertandinganBerikutnya->group?->nama ?? $pertandinganBerikutnya->babakLabel() }}
                        </p>
                        <p class="font-display mt-1 text-base font-semibold text-ink">
                            {{ $pertandinganBerikutnya->teamA?->nama ?? 'TBD' }} <span class="text-muted">vs</span> {{ $pertandinganBerikutnya->teamB?->nama ?? 'TBD' }}
                        </p>
                        <div class="mt-3 flex flex-wrap items-center gap-3 text-xs text-muted">
                            <span class="chip">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75"/></svg>
                                {{ $pertandinganBerikutnya->tanggal->format('d M Y') }}
                            </span>
                            @if($pertandinganBerikutnya->jam)
                                <span class="chip">{{ $pertandinganBerikutnya->jam }}</span>
                            @endif
                            @if($pertandinganBerikutnya->referee)
                                <span class="chip">Wasit: {{ $pertandinganBerikutnya->referee->nama }}</span>
                            @endif
                        </div>
                    </div>
                @else
                    <p class="mt-3 text-sm text-muted">Tidak ada pertandingan terjadwal.</p>
                @endif
            </div>

            @php
                $tugasAktif = collect([
                    ['label' => 'Pertandingan tanpa tanggal', 'nilai' => $tugas['tanpa_tanggal']],
                    ['label' => 'Pertandingan tanpa wasit', 'nilai' => $tugas['tanpa_wasit']],
                    ['label' => 'Tim belum masuk grup', 'nilai' => $tugas['tim_tanpa_grup']],
                ])->filter(fn ($t) => $t['nilai'] > 0);
            @endphp
            @if($tugasAktif->isNotEmpty())
                <div class="card p-5">
                    <h2 class="section-title">Perlu Ditindaklanjuti</h2>
                    <ul class="mt-3 space-y-2">
                        @foreach($tugasAktif as $t)
                            <li class="flex items-center justify-between gap-3 text-sm">
                                <span class="text-muted">{{ $t['label'] }}</span>
                                <span class="badge badge-primary font-display">{{ $t['nilai'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card p-5">
                <h2 class="section-title">Aktivitas Terbaru</h2>
                <div class="mt-3 space-y-3">
                    @forelse($aktivitas as $log)
                        <div class="flex gap-3">
                            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-gradient-to-br from-blue-600 to-cyan-500"></span>
                            <div class="min-w-0 text-sm">
                                <p class="text-ink">{{ $log->deskripsi ?: \Illuminate\Support\Str::headline($log->aksi) }}</p>
                                <p class="text-xs text-muted">
                                    {{ $log->user?->name ?? 'Sistem' }}
                                    @if($log->created_at)&middot; {{ $log->created_at->diffForHumans() }}@endif
                                </p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-muted">Belum ada aktivitas tercatat.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
