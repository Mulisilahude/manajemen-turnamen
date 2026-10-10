@extends('layouts.manage')

@section('manage')
    @php
        $qualified = collect($klasemenPerGrup)
            ->flatMap(fn ($g) => collect($g['klasemen'])->take(2)->pluck('tim_id'))
            ->values();
        $teamMap = $tournament->teams->keyBy('id');
        $pairCount = (int) floor($qualified->count() / 2);
        $groupCount = max(1, (int) floor($qualified->count() / 4));
    @endphp

    <div class="page-header">
        <div>
            <h1 class="page-title">Lanjutan Putaran</h1>
            <p class="page-subtitle">Buat babak gugur atau grup lanjutan dari tim yang lolos.</p>
        </div>
    </div>

    <div class="card panel-grad-soft mb-6 p-5 text-sm text-ink">
        <h3 class="section-title">Putaran sebelumnya: {{ $round->nama }}</h3>
        <p class="mt-1">Berdasarkan klasemen, usulan tim yang lolos (dua teratas setiap grup). Sesuaikan bila diperlukan sesuai aturan turnamen.</p>
    </div>

    @if($qualified->isEmpty())
        <div class="empty-state">
            <span class="empty-state-icon">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3h.008v.008H12v-.008ZM21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            </span>
            <p class="font-medium text-ink">Belum ada data klasemen yang cukup</p>
            <p>Selesaikan pertandingan grup terlebih dahulu.</p>
        </div>
    @else
        <div class="grid gap-5 lg:grid-cols-2">
            <div class="card p-5">
                <h3 class="section-title">Buat Babak Gugur</h3>
                <p class="mt-1 text-xs text-muted">Pertandingan tanpa hasil imbang; bila seri, atur skor adu penalti saat mengelola pertandingan.</p>
                <form method="POST" action="{{ route('admin.manage.rounds.lanjut-gugur', [$tournament, $round]) }}" class="mt-4 space-y-3">
                    @csrf
                    @for($i = 0; $i < $pairCount; $i++)
                        @php
                            $a = $qualified[$i * 2] ?? null;
                            $b = $qualified[$i * 2 + 1] ?? null;
                        @endphp
                        <div class="grid grid-cols-2 gap-2">
                            <select name="tim_a[{{ $i }}]" class="select">
                                <option value="">Pilih tim</option>
                                @foreach($qualified as $tid)
                                    <option value="{{ $tid }}" @selected($a == $tid)>{{ $teamMap[$tid]->nama ?? 'Tim #'.$tid }}</option>
                                @endforeach
                            </select>
                            <select name="tim_b[{{ $i }}]" class="select">
                                <option value="">Pilih tim</option>
                                @foreach($qualified as $tid)
                                    <option value="{{ $tid }}" @selected($b == $tid)>{{ $teamMap[$tid]->nama ?? 'Tim #'.$tid }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endfor
                    <button type="submit" class="btn btn-primary w-full">Buat Babak Gugur</button>
                </form>
            </div>

            <div class="card p-5">
                <h3 class="section-title">Buat Grup Lanjutan</h3>
                <p class="mt-1 text-xs text-muted">Setiap grup lanjutan harus berisi tepat 4 tim.</p>
                <form method="POST" action="{{ route('admin.manage.rounds.lanjut-grup', [$tournament, $round]) }}" class="mt-4 space-y-3">
                    @csrf
                    @for($g = 0; $g < $groupCount; $g++)
                        <div class="rounded-xl border border-line p-3">
                            <input type="text" name="grup[{{ $g }}][nama]" value="Grup {{ chr(65 + $g) }}" class="input mb-2">
                            <div class="grid grid-cols-2 gap-2">
                                @for($i = 0; $i < 4; $i++)
                                    @php $tid = $qualified[$g * 4 + $i] ?? null; @endphp
                                    <select name="grup[{{ $g }}][teams][]" class="select">
                                        <option value="">Pilih tim</option>
                                        @foreach($qualified as $qid)
                                            <option value="{{ $qid }}" @selected($tid == $qid)>{{ $teamMap[$qid]->nama ?? 'Tim #'.$qid }}</option>
                                        @endforeach
                                    </select>
                                @endfor
                            </div>
                        </div>
                    @endfor
                    <button type="submit" class="btn btn-outline w-full">Buat Grup Lanjutan</button>
                </form>
            </div>
        </div>
    @endif
@endsection
