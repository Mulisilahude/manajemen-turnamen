<?php

namespace App\Http\Controllers\Admin\Manage;

use App\Http\Controllers\Admin\Concerns\ResolvesTournament;
use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\Team;
use App\Models\Tournament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlayerController extends Controller
{
    use ResolvesTournament;

    public function index(Tournament $tournament): View
    {
        $teams = $tournament->teams()->with(['players' => fn ($q) => $q->orderBy('nomor_punggung')])->orderBy('nama')->get();

        return view('admin.manage.players.index', compact('tournament', 'teams'));
    }

    public function store(Request $request, Tournament $tournament): RedirectResponse
    {
        $validated = $request->validate([
            'team_id' => ['required', Rule::exists('teams', 'id')->where('tournament_id', $tournament->id)],
            'nama' => 'required|string|max:255',
            'nomor_punggung' => ['nullable', 'integer', 'min:0', 'max:999', Rule::unique('players', 'nomor_punggung')->where('team_id', (int) $request->input('team_id'))],
            'posisi' => 'nullable|string|max:100',
        ]);

        $team = Team::findOrFail($validated['team_id']);
        $this->ensureOwned($team, $tournament);

        $tournament->players()->create($validated);

        return back()->with('success', 'Pemain berhasil ditambahkan.');
    }

    public function update(Request $request, Tournament $tournament, Player $player): RedirectResponse
    {
        $this->ensureOwned($player, $tournament);

        $validated = $request->validate([
            'team_id' => ['required', Rule::exists('teams', 'id')->where('tournament_id', $tournament->id)],
            'nama' => 'required|string|max:255',
            'nomor_punggung' => ['nullable', 'integer', 'min:0', 'max:999', Rule::unique('players', 'nomor_punggung')->where('team_id', (int) $request->input('team_id'))->ignore($player->id)],
            'posisi' => 'nullable|string|max:100',
        ]);

        $player->update($validated);

        return back()->with('success', 'Pemain berhasil diperbarui.');
    }

    public function destroy(Tournament $tournament, Player $player): RedirectResponse
    {
        $this->ensureOwned($player, $tournament);
        $player->delete();

        return back()->with('success', 'Pemain berhasil dihapus.');
    }
}
