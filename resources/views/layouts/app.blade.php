@php
    $pratinjauDraft = isset($tournament)
        && ($tournament instanceof \App\Models\Tournament)
        && ! $tournament->sudahTerbit()
        && auth()->check()
        && auth()->user()->isAdmin();

    $tempatKelola = null;
    if (auth()->check() && auth()->user()->isAdmin()) {
        $tempatKelola = \App\Models\Tournament::latest()->first();
    }
@endphp
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
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
    <div class="flex min-h-screen flex-col">
        <header class="sticky top-0 z-40 border-b border-line surface/85 backdrop-blur">
            <div class="app-container flex items-center justify-between gap-4 py-3.5">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 font-display text-xl font-bold tracking-tight text-ink">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-white shadow-sm" style="background-image: var(--grad);">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />
                        </svg>
                    </span>
                    <span>Manajemen Turnamen</span>
                </a>

                <nav class="hidden items-center gap-1 text-[15px] font-medium md:flex">
                    <a href="{{ route('home') }}" class="rounded-lg px-3.5 py-2 transition {{ request()->routeIs('home') ? 'bg-primary/10 font-semibold text-primary' : 'text-muted hover:bg-surface-2 hover:text-primary' }}">Beranda</a>
                    <a href="{{ route('public.tournaments.index') }}" class="rounded-lg px-3.5 py-2 transition {{ request()->routeIs('public.tournaments.*') ? 'bg-primary/10 font-semibold text-primary' : 'text-muted hover:bg-surface-2 hover:text-primary' }}">Turnamen</a>
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="rounded-lg px-3.5 py-2 text-muted transition hover:bg-surface-2 hover:text-primary">Dashboard</a>
                        @endif
                    @endauth
                </nav>

                <div class="flex items-center gap-2">
                    @auth
                        <form method="POST" action="{{ route('logout') }}" class="hidden md:block">
                            @csrf
                            <button type="submit" class="rounded-lg px-3.5 py-2 text-[15px] font-medium text-muted transition hover:bg-surface-2 hover:text-primary">Keluar</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="hidden rounded-lg px-3.5 py-2 text-[15px] font-medium text-muted transition hover:bg-surface-2 hover:text-primary md:block">Masuk</a>
                    @endauth

                    <button type="button" data-theme-toggle class="btn btn-outline h-9 w-9 !px-0" aria-label="Ganti tema" title="Ganti tema terang/gelap">
                        <svg data-theme-icon="moon" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.25 0-9.5-4.25-9.5-9.5 0-1.083.186-2.122.522-3.085A9.003 9.003 0 0 0 3 12c0 4.97 4.03 9 9 9a8.99 8.99 0 0 0 9.75-5.998Z" />
                        </svg>
                        <svg data-theme-icon="sun" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                        </svg>
                    </button>

                    <button type="button" data-nav-toggle="#site-nav-mobile" class="btn btn-ghost h-9 w-9 !px-0 md:hidden" aria-label="Buka menu" aria-controls="site-nav-mobile" aria-expanded="false">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                    </button>
                </div>
            </div>

            <div id="site-nav-mobile" class="hidden border-t border-line surface md:hidden">
                <nav class="app-container flex flex-col gap-1 py-3 text-[15px] font-medium">
                    <a href="{{ route('home') }}" class="rounded-lg px-3 py-2.5 {{ request()->routeIs('home') ? 'bg-primary/10 font-semibold text-primary' : 'text-muted' }}">Beranda</a>
                    <a href="{{ route('public.tournaments.index') }}" class="rounded-lg px-3 py-2.5 {{ request()->routeIs('public.tournaments.*') ? 'bg-primary/10 font-semibold text-primary' : 'text-muted' }}">Turnamen</a>
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="rounded-lg px-3 py-2.5 text-muted">Dashboard</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full rounded-lg px-3 py-2.5 text-left text-muted">Keluar</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="rounded-lg px-3 py-2.5 text-muted">Masuk</a>
                    @endauth
                </nav>
            </div>
        </header>

        @if($pratinjauDraft)
            <div class="border-b border-amber-300 bg-amber-50">
                <div class="app-container flex flex-wrap items-center gap-3 py-2.5 text-sm text-amber-800">
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-300 bg-amber-100 px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wide">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                        Pratinjau Admin
                    </span>
                    <span class="font-medium">Pratinjau Admin &mdash; Belum Dipublikasikan. Pengunjung biasa tidak dapat melihat halaman ini.</span>
                    <a href="{{ route('admin.manage.impor.index', $tournament) }}" class="ml-auto rounded-lg border border-amber-300 px-2.5 py-1 text-xs font-medium transition hover:bg-amber-100">Ke halaman publikasi</a>
                </div>
            </div>
        @endif

        <main class="flex-1">
            @yield('content')
        </main>

        <footer class="mt-12 border-t border-line surface">
            <div class="grad-bar"></div>
            <div class="app-container grid gap-8 py-10 sm:grid-cols-2 lg:grid-cols-4">
                <div class="lg:col-span-2">
                    <div class="flex items-center gap-2.5 text-base font-semibold text-ink">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-white" style="background-image: var(--grad);">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497" /></svg>
                        </span>
                        Manajemen Turnamen
                    </div>
                    <p class="mt-3 max-w-md text-sm text-muted">
                        Platform terpusat untuk mengelola turnamen olahraga: jadwal, grup, klasemen, dan statistik
                        yang rapi serta mudah dipantau penonton.
                    </p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-ink">Jelajahi</h3>
                    <ul class="mt-3 space-y-2 text-sm text-muted">
                        <li><a href="{{ route('home') }}" class="transition hover:text-primary">Beranda</a></li>
                        <li><a href="{{ route('public.tournaments.index') }}" class="transition hover:text-primary">Daftar Turnamen</a></li>
                        @if($tempatKelola)
                            <li><a href="{{ route('public.tournaments.show', $tempatKelola) }}" class="transition hover:text-primary">Turnamen Terbaru</a></li>
                        @endif
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-ink">Pengelolaan</h3>
                    <ul class="mt-3 space-y-2 text-sm text-muted">
                        @auth
                            @if(auth()->user()->isAdmin())
                                <li><a href="{{ route('admin.dashboard') }}" class="transition hover:text-primary">Dashboard Admin</a></li>
                                @if($tempatKelola)
                                    <li><a href="{{ route('admin.manage.dashboard', $tempatKelola) }}" class="transition hover:text-primary">Kelola Turnamen</a></li>
                                @endif
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="transition hover:text-primary">Keluar</button>
                                    </form>
                                </li>
                            @endif
                        @else
                            <li><a href="{{ route('login') }}" class="transition hover:text-primary">Masuk Admin</a></li>
                        @endauth
                    </ul>
                </div>
            </div>
            <div class="border-t border-line">
                <div class="app-container flex flex-col items-center justify-between gap-2 py-4 text-xs text-muted sm:flex-row">
                    <p>&copy; {{ date('Y') }} Manajemen Turnamen. Seluruh hak cipta dilindungi.</p>
                    <p>Waktu pertandingan ditampilkan dalam zona WITA.</p>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
