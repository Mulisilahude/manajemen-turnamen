<?php

namespace App\Http\Controllers\Admin\Manage;

use App\Enums\PhaseType;
use App\Http\Controllers\Admin\Concerns\ResolvesTournament;
use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Round;
use App\Models\Tournament;
use App\Services\KlasemenService;
use App\Services\PutaranService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class RoundController extends Controller
{
    use ResolvesTournament;

    public function index(Tournament $tournament, KlasemenService $klasemen): View
    {
        $tournament->load(['rounds.groups.teams', 'rounds.matches.teamA', 'rounds.matches.teamB']);
        $teams = $tournament->teams()->orderBy('nama')->get();

        return view('admin.manage.rounds.index', compact('tournament', 'teams', 'klasemen'));
    }

    public function store(Request $request, Tournament $tournament, PutaranService $putaran): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'tipe' => ['required', new Enum(PhaseType::class)],
            'jumlah_grup' => 'nullable|integer|min:0|max:16',
        ]);

        $putaran->buatPutaran(
            $tournament,
            $validated['nama'],
            PhaseType::from($validated['tipe']),
            (int) ($validated['jumlah_grup'] ?? 0),
        );

        return back()->with('success', 'Putaran berhasil dibuat.');
    }

    public function destroy(Tournament $tournament, Round $round): RedirectResponse
    {
        $this->ensureOwned($round, $tournament);

        if ($round->matches()->exists()) {
            return back()->with('success', 'Putaran tidak dapat dihapus karena sudah memiliki pertandingan.');
        }

        $round->delete();

        return back()->with('success', 'Putaran berhasil dihapus.');
    }

    public function lanjut(Tournament $tournament, Round $round, KlasemenService $klasemen): View
    {
        $this->ensureOwned($round, $tournament);

        $round->load(['groups.teams']);
        $klasemenPerGrup = $klasemen->klasemenRound($round);

        return view('admin.manage.rounds.lanjut', compact('tournament', 'round', 'klasemenPerGrup'));
    }

    public function lanjutGrup(Request $request, Tournament $tournament, Round $round, PutaranService $putaran): RedirectResponse
    {
        $this->ensureOwned($round, $tournament);

        $groups = collect($request->input('grup', []))
            ->map(fn ($g) => [
                'nama' => $g['nama'] ?? null,
                'teams' => array_filter($g['teams'] ?? []),
            ])
            ->filter(fn ($g) => count($g['teams']) > 0)
            ->values()
            ->all();

        $roundBaru = $putaran->siapkanGrupLanjutan($round, $groups);

        return redirect()
            ->route('admin.manage.rounds.index', $tournament)
            ->with('success', 'Grup lanjutan '.$round->fresh()->nama.' berhasil dibuat.');
    }

    public function lanjutGugur(Request $request, Tournament $tournament, Round $round, PutaranService $putaran): RedirectResponse
    {
        $this->ensureOwned($round, $tournament);

        $pairs = [];
        foreach ((array) $request->input('tim_a', []) as $i => $a) {
            $b = $request->input("tim_b.{$i}");
            if ($a || $b) {
                $pairs[] = ['tim_a' => $a, 'tim_b' => $b];
            }
        }

        $roundBaru = $putaran->siapkanFaseGugur($round, $pairs);

        return redirect()->route('admin.manage.rounds.index', $tournament)->with('success', $roundBaru->nama.' berhasil dibuat.');
    }

    public function storeGroup(Request $request, Tournament $tournament, Round $round, PutaranService $putaran): RedirectResponse
    {
        $this->ensureOwned($round, $tournament);

        $validated = $request->validate([
            'nama' => 'required|string|max:50',
        ]);

        $putaran->buatGrup($round, $validated['nama']);

        return back()->with('success', 'Grup berhasil ditambahkan.');
    }

    public function isiGroup(Request $request, Tournament $tournament, Group $group): RedirectResponse
    {
        $this->ensureOwned($group, $tournament);

        $teamIds = (array) $request->input('team_ids', []);

        app(PutaranService::class)->isiGrup($group, $teamIds);

        return back()->with('success', 'Anggota grup berhasil disimpan.');
    }
}
