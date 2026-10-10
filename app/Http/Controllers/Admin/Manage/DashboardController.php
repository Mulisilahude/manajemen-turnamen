<?php

namespace App\Http\Controllers\Admin\Manage;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Tournament;
use App\Services\PutaranService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Tournament $tournament, PutaranService $putaran): View
    {
        $ringkasan = [
            'tim' => $tournament->teams()->count(),
            'pemain' => $tournament->players()->count(),
            'wasit' => $tournament->referees()->count(),
            'putaran' => $tournament->rounds()->count(),
            'pertandingan' => $tournament->matches()->count(),
            'selesai' => $tournament->matches()->where('status', 'selesai')->count(),
            'gol' => $tournament->matchEvents()->where('tipe', 'gol')->count(),
        ];

        $pertandinganTerbaru = $tournament->matches()
            ->with(['teamA', 'teamB', 'round', 'group'])
            ->latest('id')
            ->take(6)
            ->get();

        $pertandinganBerikutnya = $tournament->matches()
            ->with(['teamA', 'teamB', 'group', 'referee'])
            ->where('status', 'dijadwalkan')
            ->whereNotNull('tanggal')
            ->orderBy('tanggal')
            ->orderBy('jam')
            ->first();

        $aktivitas = AuditLog::query()
            ->where('tournament_id', $tournament->id)
            ->with('user')
            ->latest()
            ->take(6)
            ->get();

        $tugas = [
            'tanpa_tanggal' => $tournament->matches()->whereNull('tanggal')->count(),
            'tanpa_wasit' => $tournament->matches()->whereNull('wasit_id')->count(),
            'tim_tanpa_grup' => $tournament->teams()->whereNull('group_id')->count(),
        ];

        $juara = $putaran->juara($tournament);

        return view('admin.manage.dashboard', compact('tournament', 'ringkasan', 'pertandinganTerbaru', 'pertandinganBerikutnya', 'aktivitas', 'tugas', 'juara'));
    }
}
