@extends('layouts.manage')

@section('manage')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-ink">Pembagian Grup Manual</h2>
            <p class="mt-1 text-sm text-muted">Putaran {{ $round->nomor }}: {{ $round->nama }} &middot; {{ $round->groups->count() }} grup</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.manage.groups.undian', [$tournament, $round]) }}" class="btn btn-outline">Undian Otomatis</a>
            <a href="{{ route('admin.manage.rounds.index', $tournament) }}" class="btn btn-ghost">Kembali</a>
        </div>
    </div>

    @if($round->groups->isEmpty())
        <div class="card border-dashed p-8 text-center text-muted">
            Putaran ini belum memiliki grup. Tambahkan grup terlebih dahulu pada halaman Putaran &amp; Grup.
        </div>
    @else
        <div class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($round->groups as $group)
                <div class="card p-4">
                    <p class="text-sm font-semibold text-ink">{{ $group->nama }}</p>
                    <p class="mt-1 text-xs text-muted">{{ $group->teams->count() }} tim terdaftar</p>
                </div>
            @endforeach
        </div>

        @if($round->matches()->exists())
            <div class="alert alert-warning mb-4">
                Grup tidak dapat diubah karena putaran ini sudah memiliki jadwal atau hasil pertandingan. Hapus jadwal terlebih dahulu.
            </div>
        @endif

        <form method="POST" action="{{ route('admin.manage.groups.simpan', [$tournament, $round]) }}">
            @csrf
            <div class="card overflow-hidden">
                <div class="border-b border-line px-5 py-4">
                    <h3 class="font-semibold text-ink">Tempatkan Tim</h3>
                    <p class="mt-1 text-xs text-muted">Pilih grup untuk setiap tim. Satu tim hanya dapat berada pada satu grup.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-surface-2 text-left text-xs uppercase tracking-wide text-muted">
                            <tr>
                                <th class="px-5 py-3">Tim</th>
                                <th class="px-5 py-3">Grup</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[color:var(--line)]">
                            @forelse($teams as $team)
                                @php
                                    $current = $round->groups->first(fn ($g) => $g->teams->contains($team->id));
                                @endphp
                                <tr class="transition hover:bg-surface-2">
                                    <td class="px-5 py-3 font-medium text-ink">{{ $team->nama }}</td>
                                    <td class="px-5 py-3">
                                        <select name="penempatan[{{ $team->id }}]" @disabled($round->matches()->exists()) class="select max-w-xs">
                                            <option value="">Belum ditempatkan</option>
                                            @foreach($round->groups as $group)
                                                <option value="{{ $group->id }}" @selected(optional($current)->id === $group->id)>{{ $group->nama }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-5 py-6 text-center text-muted">Belum ada tim pada turnamen ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if(! $round->matches()->exists() && $teams->isNotEmpty())
                <div class="mt-5">
                    <button type="submit" class="btn btn-primary">Simpan Pembagian</button>
                </div>
            @endif
        </form>
    @endif
@endsection
