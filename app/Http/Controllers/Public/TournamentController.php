<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\MatchGame;
use App\Models\Tournament;
use App\Services\KlasemenService;
use App\Services\PutaranService;
use App\Services\StatistikService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class TournamentController extends Controller
{
    public function index(): View
    {
        $tournaments = Tournament::terbit()->latest()->paginate(12);

        return view('public.tournaments.index', compact('tournaments'));
    }

    public function show(Tournament $tournament): View
    {
        $this->pastikanTampil($tournament);

        $tournament->load(['rounds.groups.teams']);

        $pertandinganTerdekat = $tournament->matches()
            ->with(['teamA', 'teamB', 'round', 'group'])
            ->where('status', 'dijadwalkan')
            ->orderBy('tanggal')
            ->orderBy('jam')
            ->take(5)
            ->get();

        return view('public.tournaments.show', compact('tournament', 'pertandinganTerdekat'));
    }

    public function teams(Tournament $tournament): View
    {
        $this->pastikanTampil($tournament);

        $teams = $tournament->teams()->withCount('players')->orderBy('nama')->get();

        return view('public.tournaments.teams', compact('tournament', 'teams'));
    }

    public function standings(Tournament $tournament, KlasemenService $klasemen): View
    {
        $this->pastikanTampil($tournament);

        $tournament->load('rounds.groups.teams');

        $putaranGrup = $tournament->rounds->filter(fn ($r) => $r->isGrup() && $r->groups->isNotEmpty());

        $klasemenPerRound = $putaranGrup->mapWithKeys(fn ($round) => [
            $round->id => $klasemen->klasemenRound($round),
        ]);

        return view('public.tournaments.standings', compact('tournament', 'putaranGrup', 'klasemenPerRound'));
    }

    public function schedule(Request $request, Tournament $tournament): View
    {
        $this->pastikanTampil($tournament);

        $matches = $this->pertandinganJadwal($request, $tournament);

        $roundId = $request->integer('putaran') ?: null;
        $groupId = $request->integer('grup') ?: null;
        $tanggal = $request->input('tanggal');

        $rounds = $tournament->rounds()->orderBy('nomor')->get();
        $groups = $tournament->rounds()->with('groups')->get()->pluck('groups')->flatten()->sortBy('nama')->values();
        $tanggalTersedia = $tournament->matches()
            ->whereNotNull('tanggal')
            ->orderBy('tanggal')
            ->pluck('tanggal')
            ->map(fn ($d) => $d->format('Y-m-d'))
            ->unique()
            ->values();

        return view('public.tournaments.schedule', compact('tournament', 'matches', 'rounds', 'groups', 'tanggalTersedia', 'roundId', 'groupId', 'tanggal'));
    }

    public function liveScores(Request $request, Tournament $tournament): JsonResponse
    {
        $this->pastikanTampil($tournament);

        $matches = $this->pertandinganJadwal($request, $tournament);

        return response()->json([
            'matches' => $matches->map(fn (MatchGame $match) => [
                'id' => $match->id,
                'status' => $match->status?->value ?? $match->status,
                'label' => $match->status?->label() ?? $match->status,
                'badge' => $match->status?->badge() ?? 'badge badge-muted',
                'skor' => $match->teksSkor(),
                'ada_skor' => $match->punyaSkor(),
            ])->values(),
        ]);
    }

    /**
     * Daftar pertandingan untuk halaman jadwal beserta filternya (hanya yang belum selesai).
     */
    protected function pertandinganJadwal(Request $request, Tournament $tournament): Collection
    {
        $roundId = $request->integer('putaran') ?: null;
        $groupId = $request->integer('grup') ?: null;
        $tanggal = $request->input('tanggal');

        return $tournament->matches()
            ->with(['teamA', 'teamB', 'round', 'group'])
            ->whereIn('status', ['dijadwalkan', 'berlangsung', 'ditunda'])
            ->when($roundId, fn ($q) => $q->where('round_id', $roundId))
            ->when($groupId, fn ($q) => $q->where('group_id', $groupId))
            ->when($tanggal, fn ($q) => $q->whereDate('tanggal', $tanggal))
            ->orderBy('tanggal')
            ->orderBy('jam')
            ->get();
    }

    public function results(Tournament $tournament): View
    {
        $this->pastikanTampil($tournament);

        $matches = $tournament->matches()
            ->with(['teamA', 'teamB', 'round', 'group'])
            ->where('status', 'selesai')
            ->orderByDesc('tanggal')
            ->orderByDesc('jam')
            ->get();

        return view('public.tournaments.results', compact('tournament', 'matches'));
    }

    public function statistics(Tournament $tournament, StatistikService $statistik): View
    {
        $this->pastikanTampil($tournament);

        $topSkor = $statistik->topSkor($tournament);
        $topAssist = $statistik->topAssist($tournament);
        $kartuKuning = $statistik->kartuKuning($tournament);
        $kartuMerah = $statistik->kartuMerah($tournament);

        return view('public.tournaments.statistics', compact('tournament', 'topSkor', 'topAssist', 'kartuKuning', 'kartuMerah'));
    }

    public function bracket(Tournament $tournament, PutaranService $putaran): View
    {
        $this->pastikanTampil($tournament);

        $roundsGugur = $tournament->rounds()
            ->where('tipe', 'gugur')
            ->with(['matches.teamA', 'matches.teamB'])
            ->orderBy('nomor')
            ->get();

        $juara = $putaran->juara($tournament);

        return view('public.tournaments.bracket', compact('tournament', 'roundsGugur', 'juara'));
    }

    public function match(Tournament $tournament, MatchGame $match): View
    {
        $this->pastikanTampil($tournament);

        abort_unless((int) $match->tournament_id === (int) $tournament->id, 404);

        $match->load(['teamA', 'teamB', 'round', 'group', 'referee', 'events.player', 'events.assistPlayer', 'events.team']);

        return view('public.tournaments.match', compact('tournament', 'match'));
    }

    protected function pastikanTampil(Tournament $tournament): void
    {
        if ($tournament->sudahTerbit()) {
            return;
        }

        $admin = auth()->check() && auth()->user()->isAdmin();

        abort_unless($admin, 404);
    }
}
