<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard Admin')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
    <div class="min-h-screen flex flex-col">
        <header class="bg-gradient-to-r from-slate-950 via-blue-900 to-indigo-700 border-b border-cyan-400/40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center justify-between">
                    <a href="{{ route('admin.dashboard') }}" class="text-lg sm:text-xl font-semibold tracking-tight">
                        Dashboard Admin
                    </a>
                </div>
                <nav class="flex flex-wrap items-center gap-4 text-sm">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-cyan-300 transition">Ringkasan</a>
                    <a href="{{ route('admin.tournaments.index') }}" class="hover:text-cyan-300 transition">Daftar Turnamen</a>
                    <a href="{{ route('admin.tournaments.create') }}" class="hover:text-cyan-300 transition">Tambah Turnamen</a>
                    <a href="{{ route('home') }}" class="hover:text-cyan-300 transition">Lihat Situs Publik</a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="hover:text-cyan-300 transition">Keluar</button>
                    </form>
                </nav>
            </div>
        </header>
        <main class="flex-1 py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                @if(session('success'))
                    <div class="mb-4 rounded-xl border border-cyan-400/40 bg-cyan-500/10 px-4 py-3 text-sm">
                        {{ session('success') }}
                    </div>
                @endif
                @yield('content')
            </div>
        </main>
        <footer class="border-t border-slate-800/60 py-6 text-center text-sm text-slate-400">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                © {{ date('Y') }} Manajemen Turnamen
            </div>
        </footer>
    </div>
</body>
</html>