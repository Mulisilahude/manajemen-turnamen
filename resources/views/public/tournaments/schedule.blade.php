@extends('layouts.app')

@section('title', 'Jadwal - '.$tournament->nama)

@section('content')
    @include('public.tournaments.partials.nav', ['tournament' => $tournament])
    <div class="app-container py-8">
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-ink">Jadwal Pertandingan</h1>
            <p class="mt-1 text-sm text-muted">{{ $matches->count() }} pertandingan ditampilkan</p>
        </div>

        <form method="GET" action="{{ route('public.tournaments.schedule', $tournament) }}" class="card mb-6 grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label for="f-putaran" class="label">Putaran</label>
                <select id="f-putaran" name="putaran" class="select">
                    <option value="">Semua putaran</option>
                    @foreach($rounds as $round)
                        <option value="{{ $round->id }}" @selected((int) $roundId === (int) $round->id)>{{ $round->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="f-grup" class="label">Grup</label>
                <select id="f-grup" name="grup" class="select">
                    <option value="">Semua grup</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}" @selected((int) $groupId === (int) $group->id)>{{ $group->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="f-tanggal" class="label">Tanggal</label>
                <select id="f-tanggal" name="tanggal" class="select">
                    <option value="">Semua tanggal</option>
                    @foreach($tanggalTersedia as $tgl)
                        <option value="{{ $tgl }}" @selected($tanggal === $tgl)>{{ \Illuminate\Support\Carbon::parse($tgl)->translatedFormat('d M Y') }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn btn-primary w-full">Terapkan</button>
                @if($roundId || $groupId || $tanggal)
                    <a href="{{ route('public.tournaments.schedule', $tournament) }}" class="btn btn-outline">Reset</a>
                @endif
            </div>
        </form>

        <div class="space-y-3" data-match-endpoint="{{ route('public.tournaments.schedule.live', $tournament) }}">
            @forelse($matches as $match)
                @include('public.tournaments.partials.match-card', ['tournament' => $tournament, 'match' => $match])
            @empty
                <div class="card border border-dashed p-10 text-center text-sm text-muted">Tidak ada jadwal pertandingan yang sesuai filter.</div>
            @endforelse
        </div>
    </div>
@endsection
