<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\MatchGame;
use App\Models\Team;
use App\Models\Tournament;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $tournaments = Tournament::terbit()
            ->latest()
            ->take(6)
            ->get();

        $sorotan = Tournament::terbit()
            ->where('status', 'aktif')
            ->latest()
            ->first()
            ?? $tournaments->first();

        $pertandinganTerdekat = MatchGame::query()
            ->whereHas('tournament', fn ($q) => $q->terbit())
            ->whereIn('status', ['dijadwalkan', 'berlangsung'])
            ->whereNotNull('tanggal')
            ->with(['teamA', 'teamB', 'tournament', 'group'])
            ->orderBy('tanggal')
            ->orderBy('jam')
            ->take(4)
            ->get();

        $hasilTerbaru = MatchGame::query()
            ->whereHas('tournament', fn ($q) => $q->terbit())
            ->where('status', 'selesai')
            ->with(['teamA', 'teamB', 'tournament'])
            ->orderByDesc('tanggal')
            ->orderByDesc('jam')
            ->take(4)
            ->get();

        $jumlahTurnamen = Tournament::terbit()->count();
        $jumlahPertandingan = MatchGame::whereHas('tournament', fn ($q) => $q->terbit())->count();
        $jumlahTim = Team::whereHas('tournament', fn ($q) => $q->terbit())->count();

        return view('public.home', compact(
            'tournaments',
            'sorotan',
            'pertandinganTerdekat',
            'hasilTerbaru',
            'jumlahTurnamen',
            'jumlahPertandingan',
            'jumlahTim',
        ));
    }
}
