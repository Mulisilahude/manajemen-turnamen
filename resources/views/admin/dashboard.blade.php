@extends('layouts.admin')

@section('title', 'Ringkasan - Dashboard Admin')

@section('content')
    @php
        $ikon = fn (string $d) => '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="' . $d . '" /></svg>';

        $statistik = [
            ['label' => 'Total Turnamen', 'nilai' => \App\Models\Tournament::count(), 'icon' => $ikon('M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0')],
            ['label' => 'Draft', 'nilai' => \App\Models\Tournament::where('status', 'draft')->count(), 'icon' => $ikon('M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125')],
            ['label' => 'Aktif', 'nilai' => \App\Models\Tournament::where('status', 'aktif')->count(), 'icon' => $ikon('M5.636 5.636a9 9 0 1 0 12.728 0M12 2.25v9')],
            ['label' => 'Selesai', 'nilai' => \App\Models\Tournament::where('status', 'selesai')->count(), 'icon' => $ikon('M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z')],
        ];

        $perluDilengkapi = \App\Models\Tournament::query()
            ->where(fn ($q) => $q->whereNull('cabang_olahraga')->orWhere('cabang_olahraga', ''))
            ->get();
    @endphp

    <div class="page-header">
        <div>
            <h1 class="page-title">Ringkasan Turnamen</h1>
            <p class="page-subtitle">Pantau dan kelola semua turnamen dari satu tempat</p>
        </div>
        <a href="{{ route('admin.tournaments.create') }}" class="btn btn-primary">Tambah Turnamen</a>
    </div>

    @if($perluDilengkapi->isNotEmpty())
        <div class="alert alert-warning mb-6">
            <div class="flex items-start gap-3">
                <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                <div>
                    <p class="font-semibold">Data turnamen belum lengkap</p>
                    <p class="mt-1">Turnamen berikut belum memiliki cabang olahraga. Lengkapi agar informasi publik akurat:</p>
                    <ul class="mt-2 list-inside list-disc space-y-0.5">
                        @foreach($perluDilengkapi as $turnamen)
                            <li>
                                <a href="{{ route('admin.tournaments.edit', $turnamen) }}" class="font-medium underline underline-offset-2">{{ $turnamen->nama }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach($statistik as $stat)
            <div class="stat-card">
                <div class="flex items-start justify-between gap-3">
                    <span class="stat-icon">{!! $stat['icon'] !!}</span>
                </div>
                <p class="stat-value font-display mt-3 text-3xl text-ink">{{ $stat['nilai'] }}</p>
                <p class="stat-label mt-1">{{ $stat['label'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="card overflow-hidden">
        <div class="card-header">
            <h2 class="section-title">Daftar Turnamen</h2>
            <a href="{{ route('admin.tournaments.index') }}" class="text-sm text-primary transition hover:opacity-80">Lihat semua</a>
        </div>
        <div class="table-wrap" style="border:0;border-radius:0;box-shadow:none">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="px-5 py-3 text-left">Nama</th>
                        <th class="px-5 py-3 text-left">Cabang</th>
                        <th class="px-5 py-3 text-left">Tanggal</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(\App\Models\Tournament::latest()->take(8)->get() as $tournament)
                        <tr>
                            <td class="px-5 py-4">
                                <div class="font-medium text-ink">{{ $tournament->nama }}</div>
                                <div class="text-xs text-muted">{{ $tournament->lokasi ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-4 text-muted">{{ $tournament->cabang_olahraga ?? '-' }}</td>
                            <td class="px-5 py-4 whitespace-nowrap text-muted">{{ optional($tournament->tanggal_mulai)->format('d M Y') }} &ndash; {{ optional($tournament->tanggal_selesai)->format('d M Y') }}</td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="badge badge-primary">{{ $tournament->status?->label() ?? $tournament->status?->value ?? $tournament->status }}</span>
                                @if($tournament->tampil_publik)
                                    <span class="badge badge-success">Publik</span>
                                @else
                                    <span class="badge badge-muted">Draft</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-2 text-xs">
                                    <a href="{{ route('admin.manage.dashboard', $tournament) }}" class="btn btn-primary !rounded-lg !px-2.5 !py-1 text-xs">Kelola</a>
                                    <a href="{{ route('admin.tournaments.edit', $tournament) }}" class="btn btn-outline !rounded-lg !px-2.5 !py-1 text-xs">Edit</a>
                                    <a href="{{ route('public.tournaments.show', $tournament) }}" class="btn btn-outline !rounded-lg !px-2.5 !py-1 text-xs" target="_blank" rel="noopener">{{ $tournament->tampil_publik ? 'Lihat' : 'Pratinjau' }}</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-muted">Belum ada turnamen. Mulai dengan menambahkan turnamen pertama.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
