<?php

namespace App\Http\Controllers\Admin\Manage;

use App\Http\Controllers\Admin\Concerns\ResolvesTournament;
use App\Http\Controllers\Controller;
use App\Models\Round;
use App\Models\Tournament;
use App\Services\PutaranService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GroupController extends Controller
{
    use ResolvesTournament;

    public function kelola(Tournament $tournament, Round $round): View
    {
        $this->ensureOwned($round, $tournament);

        if (! $round->isGrup()) {
            abort(404);
        }

        $round->load('groups.teams');
        $teams = $tournament->teams()->orderBy('nama')->get();

        return view('admin.manage.rounds.grup-manual', compact('tournament', 'round', 'teams'));
    }

    public function simpan(Request $request, Tournament $tournament, Round $round, PutaranService $putaran): RedirectResponse
    {
        $this->ensureOwned($round, $tournament);

        $penempatan = (array) $request->input('penempatan', []);

        $perGrup = [];
        foreach ($round->groups as $group) {
            $perGrup[$group->id] = [];
        }

        foreach ($penempatan as $teamId => $groupId) {
            if ($groupId === '' || $groupId === null) {
                continue;
            }
            $perGrup[(int) $groupId][] = (int) $teamId;
        }

        $putaran->simpanPenempatanGrup($round, $perGrup);

        return redirect()
            ->route('admin.manage.rounds.index', $tournament)
            ->with('success', 'Pembagian grup berhasil disimpan.');
    }

    public function undian(Tournament $tournament, Round $round): View
    {
        $this->ensureOwned($round, $tournament);

        $round->load('groups.teams');
        $teams = $tournament->teams()->orderBy('nama')->get();
        $draft = session("undian_round_{$round->id}");
        $sudahDijadwalkan = $round->matches()->exists();

        return view('admin.manage.rounds.undian', compact('tournament', 'round', 'teams', 'draft', 'sudahDijadwalkan'));
    }

    public function undianProses(Request $request, Tournament $tournament, Round $round, PutaranService $putaran): RedirectResponse
    {
        $this->ensureOwned($round, $tournament);

        $validated = $request->validate([
            'jumlah_grup' => 'required|integer|min:2|max:16',
        ]);

        $groups = $putaran->generateUndian($round, (int) $validated['jumlah_grup']);

        session(["undian_round_{$round->id}" => ['jumlah_grup' => (int) $validated['jumlah_grup'], 'groups' => $groups]]);

        return redirect()
            ->route('admin.manage.groups.undian', [$tournament, $round])
            ->with('success', 'Pratinjau undian dibuat. Periksa hasil sebelum disahkan.');
    }

    public function undianSahkan(Tournament $tournament, Round $round, PutaranService $putaran): RedirectResponse
    {
        $this->ensureOwned($round, $tournament);

        $draft = session("undian_round_{$round->id}");

        if (! $draft || empty($draft['groups'])) {
            return back()->with('error', 'Tidak ada pratinjau undian untuk disahkan.');
        }

        $putaran->sahkanUndian($round, $draft['groups']);

        session()->forget("undian_round_{$round->id}");

        return redirect()
            ->route('admin.manage.groups.kelola', [$tournament, $round])
            ->with('success', 'Undian berhasil disahkan.');
    }

    public function undianBatal(Tournament $tournament, Round $round): RedirectResponse
    {
        $this->ensureOwned($round, $tournament);

        session()->forget("undian_round_{$round->id}");

        return redirect()
            ->route('admin.manage.groups.undian', [$tournament, $round])
            ->with('success', 'Pratinjau undian dibatalkan.');
    }
}
