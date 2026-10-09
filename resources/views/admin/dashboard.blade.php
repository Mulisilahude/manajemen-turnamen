@extends('layouts.admin')

@section('title', 'Ringkasan - Dashboard Admin')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl sm:text-3xl font-semibold">Ringkasan Turnamen</h1>
        <p class="mt-2 text-sm text-slate-400">Pantau dan kelola semua turnamen dari satu tempat</p>
    </div>
    <a href="{{ route('admin.tournaments.create') }}" class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-blue-600 to-cyan-500 px-5 py-2.5 text-sm font-medium transition hover:from-blue-500 hover:to-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-400/40">
        Tambah Turnamen Baru
    </a>
</div>

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-8">
    @php
        $total = \App\Models\Tournament::count();
        $draft = \App\Models\Tournament::where('status', \App\TournamentStatus::DRAFT)->count();
        $active = \App\Models\Tournament::where('status', \App\TournamentStatus::ACTIVE)->count();
        $completed = \App\Models\Tournament::where('status', \App\TournamentStatus::COMPLETED)->count();
    @endphp
    <div class="rounded-2xl border border-slate-800 bg-slate-900/40 p-5">
        <p class="text-sm text-slate-400">Total Turnamen</p>
        <p class="mt-2 text-2xl font-semibold">{{ $total }}</p>
    </div>
    <div class="rounded-2xl border border-slate-800 bg-slate-900/40 p-5">
        <p class="text-sm text-slate-400">Draft</p>
        <p class="mt-2 text-2xl font-semibold">{{ $draft }}</p>
    </div>
    <div class="rounded-2xl border border-slate-800 bg-slate-900/40 p-5">
        <p class="text-sm text-slate-400">Aktif</p>
        <p class="mt-2 text-2xl font-semibold">{{ $active }}</p>
    </div>
    <div class="rounded-2xl border border-slate-800 bg-slate-900/40 p-5">
        <p class="text-sm text-slate-400">Selesai</p>
        <p class="mt-2 text-2xl font-semibold">{{ $completed }}</p>
    </div>
</div>

<div class="rounded-2xl border border-slate-800 bg-slate-900/40">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-5 border-b border-slate-800">
        <h2 class="text-lg font-semibold">Daftar Turnamen</h2>
        <a href="{{ route('admin.tournaments.index') }}" class="text-sm text-cyan-300 hover:text-cyan-200 transition">Lihat semua</a>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-800 text-sm">
            <thead class="bg-slate-900/60">
                <tr>
                    <th class="px-5 py-3 text-left text-xs uppercase tracking-wider text-slate-400">Nama</th>
                    <th class="px-5 py-3 text-left text-xs uppercase tracking-wider text-slate-400">Cabang</th>
                    <th class="px-5 py-3 text-left text-xs uppercase tracking-wider text-slate-400">Tanggal</th>
                    <th class="px-5 py-3 text-left text-xs uppercase tracking-wider text-slate-400">Status</th>
                    <th class="px-5 py-3 text-left text-xs uppercase tracking-wider text-slate-400">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse(\App\Models\Tournament::latest()->take(10)->get() as $tournament)
                    <tr class="hover:bg-slate-900/50">
                        <td class="px-5 py-4 whitespace-nowrap">
                            <div class="font-medium">{{ $tournament->nama }}</div>
                            <div class="text-xs text-slate-400">{{ $tournament->lokasi ?? '-' }}</div>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap text-slate-300">{{ $tournament->cabang_olahraga ?? '-' }}</td>
                        <td class="px-5 py-4 whitespace-nowrap text-slate-300">{{ optional($tournament->tanggal_mulai)->format('d M Y') }} - {{ optional($tournament->tanggal_selesai)->format('d M Y') }}</td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <span class="rounded-full border border-cyan-400/40 bg-cyan-500/10 px-2.5 py-1 text-xs text-cyan-200">{{ $tournament->status?->value ?? $tournament->status }}</span>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap flex flex-wrap gap-2 text-xs">
                            <a href="{{ route('admin.tournaments.edit', $tournament) }}" class="rounded-lg border border-slate-700 px-2.5 py-1 transition hover:border-cyan-400/60 hover:text-cyan-300">Edit</a>
                            <form method="POST" action="{{ route('admin.tournaments.activate', $tournament) }}">
                                @csrf
                                <button type="submit" class="rounded-lg border border-emerald-500/40 bg-emerald-500/10 px-2.5 py-1 text-emerald-200 transition hover:bg-emerald-500/20">Pilih untuk dikelola</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-slate-400">Belum ada turnamen. Mulai dengan menambahkan turnamen pertama.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection