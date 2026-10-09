@extends('layouts.admin')

@section('title', 'Daftar Turnamen - Dashboard Admin')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl sm:text-3xl font-semibold">Daftar Turnamen</h1>
        <p class="mt-2 text-sm text-slate-400">Kelola semua turnamen yang telah dibuat</p>
    </div>
    <a href="{{ route('admin.tournaments.create') }}" class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-blue-600 to-cyan-500 px-5 py-2.5 text-sm font-medium transition hover:from-blue-500 hover:to-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-400/40">
        Tambah Turnamen
    </a>
</div>

<div class="rounded-2xl border border-slate-800 bg-slate-900/40 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-800 text-sm">
            <thead class="bg-slate-900/60">
                <tr>
                    <th class="px-5 py-3 text-left text-xs uppercase tracking-wider text-slate-400">Nama</th>
                    <th class="px-5 py-3 text-left text-xs uppercase tracking-wider text-slate-400">Cabang</th>
                    <th class="px-5 py-3 text-left text-xs uppercase tracking-wider text-slate-400">Tanggal</th>
                    <th class="px-5 py-3 text-left text-xs uppercase tracking-wider text-slate-400">Lokasi</th>
                    <th class="px-5 py-3 text-left text-xs uppercase tracking-wider text-slate-400">Status</th>
                    <th class="px-5 py-3 text-left text-xs uppercase tracking-wider text-slate-400">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($tournaments as $tournament)
                    <tr class="hover:bg-slate-900/50">
                        <td class="px-5 py-4">
                            <div class="font-medium">{{ $tournament->nama }}</div>
                            <div class="mt-1 text-xs text-slate-400">Slug: {{ $tournament->slug }}</div>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap text-slate-300">{{ $tournament->cabang_olahraga ?? '-' }}</td>
                        <td class="px-5 py-4 whitespace-nowrap text-slate-300">{{ optional($tournament->tanggal_mulai)->format('d M Y') }} - {{ optional($tournament->tanggal_selesai)->format('d M Y') }}</td>
                        <td class="px-5 py-4 whitespace-nowrap text-slate-300">{{ $tournament->lokasi ?? '-' }}</td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <span class="rounded-full border border-cyan-400/40 bg-cyan-500/10 px-2.5 py-1 text-xs text-cyan-200">{{ $tournament->status?->value ?? $tournament->status }}</span>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap flex flex-wrap gap-2 text-xs">
                            <a href="{{ route('admin.tournaments.edit', $tournament) }}" class="rounded-lg border border-slate-700 px-2.5 py-1 transition hover:border-cyan-400/60 hover:text-cyan-300">Edit</a>
                            <form method="POST" action="{{ route('admin.tournaments.activate', $tournament) }}">
                                @csrf
                                <button type="submit" class="rounded-lg border border-emerald-500/40 bg-emerald-500/10 px-2.5 py-1 text-emerald-200 transition hover:bg-emerald-500/20">Pilih untuk dikelola</button>
                            </form>
                            <a href="{{ route('public.tournaments.show', $tournament) }}" class="rounded-lg border border-slate-700 px-2.5 py-1 transition hover:border-cyan-400/60 hover:text-cyan-300" target="_blank" rel="noopener">Lihat Publik</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-slate-400">Belum ada turnamen.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">
    {{ $tournaments->links() }}
</div>
@endsection