@extends('layouts.app')

@section('title', 'Klasemen - '.$tournament->nama)

@section('content')
    @include('public.tournaments.partials.nav', ['tournament' => $tournament])
    <div class="app-container py-8">
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-ink">Klasemen</h1>
            <p class="mt-1 text-sm text-muted">Peringkat tim berdasarkan hasil pertandingan fase grup.</p>
        </div>

        @forelse($putaranGrup as $round)
            <div class="mb-9">
                <h2 class="mb-4 text-base font-semibold text-ink">{{ $round->nama }}</h2>
                @foreach($klasemenPerRound[$round->id] as $bagian)
                    <div class="card mb-5 overflow-hidden">
                        <div class="flex items-center justify-between gap-3 border-b border-line surface-muted px-4 py-2.5">
                            <span class="text-sm font-semibold text-ink">{{ $bagian['grup']->nama }}</span>
                            <span class="text-xs text-muted">{{ count($bagian['klasemen']) }} tim</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2.5 text-left">#</th>
                                        <th class="px-4 py-2.5 text-left">Tim</th>
                                        <th class="px-3 py-2.5 text-center">Main</th>
                                        <th class="px-3 py-2.5 text-center">M</th>
                                        <th class="px-3 py-2.5 text-center">S</th>
                                        <th class="px-3 py-2.5 text-center">K</th>
                                        <th class="px-3 py-2.5 text-center">GM</th>
                                        <th class="px-3 py-2.5 text-center">GK</th>
                                        <th class="px-3 py-2.5 text-center">SG</th>
                                        <th class="px-4 py-2.5 text-center">Poin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bagian['klasemen'] as $row)
                                        <tr class="{{ $row['seri_penuh'] ? 'bg-amber-50' : '' }}">
                                            <td class="px-4 py-2.5">
                                                <span class="rank-marker {{ $row['peringkat'] <= 2 ? 'rank-marker-lolos' : '' }}">{{ $row['peringkat'] }}</span>
                                            </td>
                                            <td class="px-4 py-2.5 font-medium text-ink">{{ $row['nama'] }}</td>
                                            <td class="px-3 py-2.5 text-center text-muted">{{ $row['main'] }}</td>
                                            <td class="px-3 py-2.5 text-center text-muted">{{ $row['menang'] }}</td>
                                            <td class="px-3 py-2.5 text-center text-muted">{{ $row['seri'] }}</td>
                                            <td class="px-3 py-2.5 text-center text-muted">{{ $row['kalah'] }}</td>
                                            <td class="px-3 py-2.5 text-center text-muted">{{ $row['gm'] }}</td>
                                            <td class="px-3 py-2.5 text-center text-muted">{{ $row['gk'] }}</td>
                                            <td class="px-3 py-2.5 text-center text-muted">{{ $row['sg'] }}</td>
                                            <td class="px-4 py-2.5 text-center stat-value text-ink">{{ $row['poin'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        @empty
            <div class="card border-dashed p-10 text-center text-sm text-muted">Belum ada data klasemen.</div>
        @endforelse
    </div>
@endsection
