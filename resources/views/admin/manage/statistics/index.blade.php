@extends('layouts.manage')

@section('manage')
    <div class="page-header">
        <div>
            <h1 class="page-title">Statistik</h1>
            <p class="page-subtitle">Peringkat pemain berdasarkan kejadian pertandingan.</p>
        </div>
        <form method="GET" class="flex items-center gap-2 text-sm">
            <label class="field-label !mb-0" for="f-round">Putaran</label>
            <select id="f-round" name="round" onchange="this.form.submit()" class="select">
                <option value="">Seluruh turnamen</option>
                @foreach($rounds as $round)
                    <option value="{{ $round->id }}" @selected($roundId == $round->id)>Putaran {{ $round->nomor }}: {{ $round->nama }}</option>
                @endforeach
            </select>
        </form>
    </div>

    @php
        $tabel = [
            ['judul' => 'Top Skor', 'data' => $topSkor, 'label' => 'gol', 'relasi' => 'player'],
            ['judul' => 'Top Assist', 'data' => $topAssist, 'label' => 'assist', 'relasi' => 'assistPlayer'],
            ['judul' => 'Kartu Kuning', 'data' => $kartuKuning, 'label' => 'kartu', 'relasi' => 'player'],
            ['judul' => 'Kartu Merah', 'data' => $kartuMerah, 'label' => 'kartu', 'relasi' => 'player'],
        ];
    @endphp

    <div class="grid gap-5 md:grid-cols-2">
        @foreach($tabel as $bagian)
            <div class="card overflow-hidden">
                <div class="card-header">
                    <h3 class="section-title">{{ $bagian['judul'] }}</h3>
                </div>
                <div class="table-wrap" style="border:0;border-radius:0;box-shadow:none">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left">#</th>
                                <th class="px-4 py-2 text-left">Pemain</th>
                                <th class="px-4 py-2 text-left">Tim</th>
                                <th class="px-4 py-2 text-center">{{ ucfirst($bagian['label']) }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bagian['data'] as $i => $row)
                                <tr>
                                    <td class="px-4 py-2">
                                        <span class="rank-marker {{ $i === 0 ? 'rank-marker-lolos' : '' }}">{{ $i + 1 }}</span>
                                    </td>
                                    <td class="px-4 py-2 font-medium text-ink">{{ $row->{$bagian['relasi']}?->nama ?? '-' }}</td>
                                    <td class="px-4 py-2 text-muted">{{ $row->team?->nama ?? '-' }}</td>
                                    <td class="px-4 py-2 text-center"><span class="stat-value font-display text-lg text-ink">{{ $row->jumlah }}</span></td>
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
@endsection
