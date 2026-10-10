@extends('layouts.admin')

@section('title', 'Daftar Turnamen - Dashboard Admin')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Daftar Turnamen</h1>
            <p class="page-subtitle">Kelola semua turnamen yang telah dibuat.</p>
        </div>
        <a href="{{ route('admin.tournaments.create') }}" class="btn btn-primary">Tambah Turnamen</a>
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="px-5 py-3 text-left">Nama</th>
                    <th class="px-5 py-3 text-left">Cabang</th>
                    <th class="px-5 py-3 text-left">Tanggal</th>
                    <th class="px-5 py-3 text-left">Lokasi</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tournaments as $tournament)
                    <tr>
                        <td class="px-5 py-4">
                            <div class="font-medium text-ink">{{ $tournament->nama }}</div>
                            <div class="mt-0.5 text-xs text-muted">{{ $tournament->slug }}</div>
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap text-muted">{{ $tournament->cabang_olahraga ?? '-' }}</td>
                        <td class="px-5 py-4 whitespace-nowrap text-muted">{{ optional($tournament->tanggal_mulai)->format('d M Y') }} &ndash; {{ optional($tournament->tanggal_selesai)->format('d M Y') }}</td>
                        <td class="px-5 py-4 whitespace-nowrap text-muted">{{ $tournament->lokasi ?? '-' }}</td>
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
                                <form method="POST" action="{{ route('admin.tournaments.activate', $tournament) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline !rounded-lg !px-2.5 !py-1 text-xs">Aktifkan</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-muted">Belum ada turnamen.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $tournaments->links() }}
    </div>
@endsection
