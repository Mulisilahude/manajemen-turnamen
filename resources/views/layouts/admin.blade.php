<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard Admin')</title>
    <script>
        (function () {
            try {
                var tersimpan = localStorage.getItem('theme');
                var sistemGelap = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (tersimpan === 'dark' || (!tersimpan && sistemGelap)) {
                    document.documentElement.classList.add('dark');
                }
            } catch (e) {}
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-canvas text-ink antialiased">
    @php
        $ikon = fn (string $d) => '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="' . $d . '" /></svg>';

        $grupUtama = [
            [
                'label' => 'Ringkasan',
                'route' => 'admin.dashboard',
                'match' => 'admin.dashboard',
                'icon' => $ikon('M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z'),
            ],
            [
                'label' => 'Turnamen',
                'route' => 'admin.tournaments.index',
                'match' => ['admin.tournaments.index', 'admin.tournaments.edit', 'admin.tournaments.show'],
                'icon' => $ikon('M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0'),
            ],
            [
                'label' => 'Tambah Turnamen',
                'route' => 'admin.tournaments.create',
                'match' => 'admin.tournaments.create',
                'icon' => $ikon('M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'),
            ],
        ];

        $grupKelola = isset($tournament) && $tournament ? [
            [
                'label' => 'Ringkasan',
                'route' => 'admin.manage.dashboard',
                'match' => 'admin.manage.dashboard',
                'icon' => $ikon('M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z'),
            ],
            [
                'label' => 'Jadwal & Pertandingan',
                'route' => 'admin.manage.matches.index',
                'match' => 'admin.manage.matches.*',
                'icon' => $ikon('M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5'),
            ],
            [
                'label' => 'Grup & Putaran',
                'route' => 'admin.manage.rounds.index',
                'match' => ['admin.manage.rounds.*', 'admin.manage.jadwal.*'],
                'icon' => $ikon('M6 6.878V6a2.25 2.25 0 0 1 2.25-2.25h7.5A2.25 2.25 0 0 1 18 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 0 0 4.5 9v.878m13.5-3A2.25 2.25 0 0 1 19.5 9v.878m0 0a2.246 2.246 0 0 0-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0 1 21 12v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6c0-.98.626-1.813 1.5-2.122'),
            ],
            [
                'label' => 'Tim',
                'route' => 'admin.manage.teams.index',
                'match' => 'admin.manage.teams.*',
                'icon' => $ikon('M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z'),
            ],
            [
                'label' => 'Pemain',
                'route' => 'admin.manage.players.index',
                'match' => 'admin.manage.players.*',
                'icon' => $ikon('M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z'),
            ],
            [
                'label' => 'Wasit',
                'route' => 'admin.manage.referees.index',
                'match' => 'admin.manage.referees.*',
                'icon' => $ikon('M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z'),
            ],
            [
                'label' => 'Statistik',
                'route' => 'admin.manage.statistics.index',
                'match' => 'admin.manage.statistics.*',
                'icon' => $ikon('M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z'),
            ],
            [
                'label' => 'Impor Resmi',
                'route' => 'admin.manage.impor.index',
                'match' => 'admin.manage.impor.*',
                'icon' => $ikon('M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 7.5 7.5 12M12 7.5V16.5'),
            ],
        ] : null;

        $grupSitus = [
            [
                'label' => 'Situs Publik',
                'route' => 'home',
                'match' => 'home',
                'icon' => $ikon('M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0 0a8.949 8.949 0 0 0 4.951-1.488A3.987 3.987 0 0 0 13 16h-2a3.987 3.987 0 0 0-3.951 3.512A8.948 8.948 0 0 0 12 21Zm3-11.25a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z'),
            ],
        ];

        $pengguna = auth()->user();
    @endphp

    {{-- Sidebar utama (desktop) --}}
    <aside class="admin-sidebar fixed inset-y-0 left-0 z-40 hidden w-72 flex-col lg:flex">
        <div class="flex h-16 items-center gap-2.5 px-5">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 via-cyan-500 to-cyan-400 text-white shadow-sm">
                <svg class="h-4.5 w-4.5" style="width:1.15rem;height:1.15rem" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" /></svg>
            </span>
            <div class="leading-tight">
                <a href="{{ route('admin.dashboard') }}" class="font-display text-sm font-semibold text-ink">Admin Turnamen</a>
                <p class="text-[11px] text-muted">Panel pengelolaan</p>
            </div>
        </div>

        <nav class="flex-1 space-y-5 overflow-y-auto px-3 pb-4">
            <div class="nav-group">
                <p class="nav-group-title">Utama</p>
                <div class="space-y-1">
                    @foreach($grupUtama as $item)
                        @php $aktif = request()->routeIs(...(array) $item['match']); @endphp
                        <a href="{{ route($item['route']) }}" class="nav-item {{ $aktif ? 'nav-item-active' : '' }}" @if($aktif) aria-current="page" @endif>
                            {!! $item['icon'] !!}
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            @if($grupKelola)
                <div class="nav-group">
                    <p class="nav-group-title">Kelola · {{ \Illuminate\Support\Str::limit($tournament->nama, 22) }}</p>
                    <div class="space-y-1">
                        @foreach($grupKelola as $item)
                            @php $aktif = request()->routeIs(...(array) $item['match']); @endphp
                            <a href="{{ route($item['route'], $tournament) }}" class="nav-item {{ $aktif ? 'nav-item-active' : '' }}" @if($aktif) aria-current="page" @endif>
                                {!! $item['icon'] !!}
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="nav-group">
                <p class="nav-group-title">Situs</p>
                <div class="space-y-1">
                    @foreach($grupSitus as $item)
                        @php $aktif = request()->routeIs(...(array) $item['match']); @endphp
                        <a href="{{ route($item['route']) }}" class="nav-item {{ $aktif ? 'nav-item-active' : '' }}">
                            {!! $item['icon'] !!}
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </nav>

        <div class="border-t border-line p-3">
            <div class="flex items-center gap-3 rounded-xl bg-surface-2 px-3 py-2.5">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-blue-600 to-cyan-500 text-sm font-semibold text-white">
                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($pengguna?->name ?? 'A', 0, 1)) }}
                </span>
                <div class="min-w-0 flex-1 leading-tight">
                    <p class="truncate text-sm font-medium text-ink">{{ $pengguna?->name ?? 'Administrator' }}</p>
                    <p class="truncate text-[11px] text-muted">{{ $pengguna?->email ?? 'admin' }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-ghost h-8 w-8 !px-0" title="Keluar" aria-label="Keluar">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" /></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Laci navigasi (mobile) --}}
    <div id="admin-drawer" class="fixed inset-0 z-50 hidden lg:hidden">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" data-nav-toggle="#admin-drawer"></div>
        <div class="admin-sidebar relative flex h-full w-72 max-w-[85%] flex-col shadow-2xl">
            <div class="flex h-16 items-center justify-between px-5">
                <a href="{{ route('admin.dashboard') }}" class="font-display text-sm font-semibold text-ink">Admin Turnamen</a>
                <button type="button" data-nav-toggle="#admin-drawer" class="btn btn-ghost h-8 w-8 !px-0" aria-label="Tutup menu">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <nav class="flex-1 space-y-5 overflow-y-auto px-3 pb-4">
                <div class="nav-group">
                    <p class="nav-group-title">Utama</p>
                    <div class="space-y-1">
                        @foreach($grupUtama as $item)
                            @php $aktif = request()->routeIs(...(array) $item['match']); @endphp
                            <a href="{{ route($item['route']) }}" class="nav-item {{ $aktif ? 'nav-item-active' : '' }}">
                                {!! $item['icon'] !!}
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
                @if($grupKelola)
                    <div class="nav-group">
                        <p class="nav-group-title">Kelola · {{ \Illuminate\Support\Str::limit($tournament->nama, 22) }}</p>
                        <div class="space-y-1">
                            @foreach($grupKelola as $item)
                                @php $aktif = request()->routeIs(...(array) $item['match']); @endphp
                                <a href="{{ route($item['route'], $tournament) }}" class="nav-item {{ $aktif ? 'nav-item-active' : '' }}">
                                    {!! $item['icon'] !!}
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
                <div class="nav-group">
                    <p class="nav-group-title">Situs</p>
                    <div class="space-y-1">
                        @foreach($grupSitus as $item)
                            <a href="{{ route($item['route']) }}" class="nav-item">
                                {!! $item['icon'] !!}
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </nav>
            <div class="border-t border-line p-3">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline w-full">Keluar</button>
                </form>
            </div>
        </div>
    </div>

    <div class="flex min-h-screen flex-col lg:pl-72">
        <header class="sticky top-0 z-30 flex h-16 items-center justify-between gap-3 border-b border-line surface px-4 sm:px-6 lg:px-8">
            <div class="flex min-w-0 items-center gap-2">
                <button type="button" data-nav-toggle="#admin-drawer" class="btn btn-ghost h-9 w-9 !px-0 lg:hidden" aria-label="Buka menu">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                </button>
                <span class="truncate text-sm text-muted">
                    @hasSection('breadcrumb')
                        @yield('breadcrumb')
                    @else
                        Dashboard pengelolaan turnamen
                    @endif
                </span>
            </div>
            <div class="flex items-center gap-2">
                @if(isset($tournament) && $tournament)
                    <a href="{{ route('public.tournaments.show', $tournament) }}" target="_blank" class="btn btn-outline h-9 hidden text-xs sm:inline-flex">Lihat Publik</a>
                @endif
                <button type="button" data-theme-toggle class="btn btn-ghost h-9 w-9 !px-0" aria-label="Ganti tema">
                    <svg data-theme-icon="moon" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" /></svg>
                    <svg data-theme-icon="sun" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" /></svg>
                </button>
                <details class="relative">
                    <summary class="flex cursor-pointer list-none items-center gap-2 rounded-full border border-line bg-surface-2 py-1 pe-3 pl-1 pr-3 transition hover:border-primary/40 marker:content-none">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-blue-600 to-cyan-500 text-xs font-semibold text-white">
                            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($pengguna?->name ?? 'A', 0, 1)) }}
                        </span>
                        <span class="hidden text-sm font-medium text-ink sm:block">{{ $pengguna?->name ?? 'Administrator' }}</span>
                    </summary>
                </details>
            </div>
        </header>

        <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">
                @if(session('success'))
                    <div class="alert alert-success mb-5">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-error mb-5">{{ session('error') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-error mb-5">
                        <p class="font-medium">Periksa kembali isian berikut:</p>
                        <ul class="mt-1 list-inside list-disc space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>

        <footer class="border-t border-line surface py-5">
            <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-2 px-4 text-xs text-muted sm:px-6 lg:px-8">
                <span>&copy; {{ date('Y') }} Admin Turnamen</span>
                <span>Dibuat dengan Laravel</span>
            </div>
        </footer>
    </div>
</body>
</html>
