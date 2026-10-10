<?php

namespace App\Http\Controllers\Admin\Manage;

use App\Http\Controllers\Admin\Concerns\ResolvesTournament;
use App\Http\Controllers\Controller;
use App\Models\Referee;
use App\Models\Tournament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RefereeController extends Controller
{
    use ResolvesTournament;

    public function index(Tournament $tournament): View
    {
        $referees = $tournament->referees()->withCount('matches')->orderBy('nama')->get();

        return view('admin.manage.referees.index', compact('tournament', 'referees'));
    }

    public function store(Request $request, Tournament $tournament): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nomor_kontak' => 'nullable|string|max:50',
        ]);

        $tournament->referees()->create($validated);

        return back()->with('success', 'Wasit berhasil ditambahkan.');
    }

    public function update(Request $request, Tournament $tournament, Referee $referee): RedirectResponse
    {
        $this->ensureOwned($referee, $tournament);

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nomor_kontak' => 'nullable|string|max:50',
        ]);

        $referee->update($validated);

        return back()->with('success', 'Wasit berhasil diperbarui.');
    }

    public function destroy(Tournament $tournament, Referee $referee): RedirectResponse
    {
        $this->ensureOwned($referee, $tournament);
        $referee->delete();

        return back()->with('success', 'Wasit berhasil dihapus.');
    }
}
