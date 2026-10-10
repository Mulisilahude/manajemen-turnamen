<?php

namespace App\Http\Controllers\Admin\Manage;

use App\Enums\RegistrationStatus;
use App\Http\Controllers\Admin\Concerns\ResolvesTournament;
use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\Tournament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class TeamController extends Controller
{
    use ResolvesTournament;

    public function index(Tournament $tournament): View
    {
        $teams = $tournament->teams()->withCount('players')->orderBy('nama')->get();

        return view('admin.manage.teams.index', compact('tournament', 'teams'));
    }

    public function store(Request $request, Tournament $tournament): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255', Rule::unique('teams', 'nama')->where('tournament_id', $tournament->id)],
            'singkatan' => 'nullable|string|max:10',
            'logo' => 'nullable|string|max:255',
            'pelatih' => 'nullable|string|max:255',
            'status_pendaftaran' => ['required', new Enum(RegistrationStatus::class)],
        ]);

        $tournament->teams()->create($validated);

        return back()->with('success', 'Tim berhasil ditambahkan.');
    }

    public function update(Request $request, Tournament $tournament, Team $team): RedirectResponse
    {
        $this->ensureOwned($team, $tournament);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255', Rule::unique('teams', 'nama')->where('tournament_id', $tournament->id)->ignore($team->id)],
            'singkatan' => 'nullable|string|max:10',
            'logo' => 'nullable|string|max:255',
            'pelatih' => 'nullable|string|max:255',
            'status_pendaftaran' => ['required', new Enum(RegistrationStatus::class)],
        ]);

        $team->update($validated);

        return back()->with('success', 'Tim berhasil diperbarui.');
    }

    public function destroy(Tournament $tournament, Team $team): RedirectResponse
    {
        $this->ensureOwned($team, $tournament);
        $team->delete();

        return back()->with('success', 'Tim berhasil dihapus.');
    }
}
