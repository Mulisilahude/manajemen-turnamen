@extends('layouts.app')

@section('title', 'Statistik - '.$tournament->nama)

@section('content')
    @include('public.tournaments.partials.nav', ['tournament' => $tournament])
    <div class="app-container py-8">
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-ink">Statistik Turnamen</h1>
            <p class="mt-1 text-sm text-muted">Pencapaian pemain berdasarkan kejadian pertandingan yang tercatat.</p>
        </div>

        @php
            $tabel = [
                ['judul' => 'Top Skor', 'data' => $topSkor, 'relasi' => 'player'],
                ['judul' => 'Top Assist', 'data' => $topAssist, 'relasi' => 'assistPlayer'],
                ['judul' => 'Kartu Kuning', 'data' => $kartuKuning, 'relasi' => 'player'],
                ['judul' => 'Kartu Merah', 'data' => $kartuMerah, 'relasi' => 'player'],
            ];
        @endphp

        <div class="grid gap-5 md:grid-cols-2">
            @foreach($tabel as $bagian)
                <div class="card overflow-hidden">
                    <div class="flex items-center justify-between gap-3 border-b border-line surface-muted px-5 py-3">
                        <h2 class="text-sm font-semibold text-ink">{{ $bagian['judul'] }}</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2.5 text-left">#</th>
                                    <th class="px-4 py-2.5 text-left">Pemain</th>
                                    <th class="px-4 py-2.5 text-left">Tim</th>
                                    <th class="px-4 py-2.5 text-center">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bagian['data'] as $i => $row)
                                    <tr>
                                        <td class="px-4 py-2.5"><span class="rank-marker {{ $i === 0 ? 'rank-marker-lolos' : '' }}">{{ $i + 1 }}</span></td>
                                        <td class="px-4 py-2.5 font-medium text-ink">{{ $row->{$bagian['relasi']}?->nama ?? '-' }}</td>
                                        <td class="px-4 py-2.5 text-muted">{{ $row->team?->nama ?? '-' }}</td>
                                        <td class="px-4 py-2.5 text-center font-semibold text-ink">{{ $row->jumlah }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-4 py-8 text-center text-muted">Belum ada data.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
