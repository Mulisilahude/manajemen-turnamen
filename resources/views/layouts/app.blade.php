<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
    <div class="min-h-screen flex flex-col">
        <header class="bg-gradient-to-r from-slate-950 via-blue-900 to-cyan-700 border-b border-cyan-500/40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                <a href="{{ route('home') }}" class="text-lg sm:text-xl font-semibold tracking-tight">
                    Manajemen Turnamen
                </a>
                <nav class="flex items-center gap-4 text-sm">
                    <a href="{{ route('home') }}" class="hover:text-cyan-300 transition">Beranda</a>
                    <a href="{{ route('public.tournaments.index') }}" class="hover:text-cyan-300 transition">Daftar Turnamen</a>
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="hover:text-cyan-300 transition">Dashboard Admin</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="hover:text-cyan-300 transition">Keluar</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="hover:text-cyan-300 transition">Masuk Admin</a>
                    @endauth
                </nav>
            </div>
        </header>
        <main class="flex-1">
            @yield('content')
        </main>
        <footer class="border-t border-slate-800/60 py-6 text-center text-sm text-slate-400">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                © {{ date('Y') }} Manajemen Turnamen
            </div>
        </footer>
    </div>
</body>
</html>