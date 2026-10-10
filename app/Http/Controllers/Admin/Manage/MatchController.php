<?php

namespace App\Http\Controllers\Admin\Manage;

use App\Http\Controllers\Admin\Concerns\ResolvesTournament;
use App\Http\Controllers\Controller;
use App\Models\MatchGame;
use App\Models\Tournament;
use App\Services\PertandinganService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MatchController extends Controller
{
    use ResolvesTournament;

    public function index(Request $request, Tournament $tournament): View
    {
        $rounds = $tournament->rounds()->orderBy('nomor')->get();
        $groups = $tournament->groups()->orderBy('nama')->get();
        $roundId = $request->integer('round');
        $groupId = $request->integer('group');
        $tanggal = $request->filled('tanggal') ? $request->string('tanggal')->toString() : null;
        $status = $request->filled('status') ? $request->string('status')->toString() : null;
        $cari = trim((string) $request->query('q', ''));

        $matches = $tournament->matches()
            ->with(['teamA', 'teamB', 'round', 'group', 'referee'])
            ->when($roundId, fn ($q) => $q->where('round_id', $roundId))
            ->when($groupId, fn ($q) => $q->where('group_id', $groupId))
            ->when($tanggal, fn ($q) => $q->whereDate('tanggal', $tanggal))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($cari !== '', fn ($q) => $q->where(function ($w) use ($cari) {
                $w->whereHas('teamA', fn ($t) => $t->where('nama', 'like', "%{$cari}%"))
                    ->orWhereHas('teamB', fn ($t) => $t->where('nama', 'like', "%{$cari}%"));
            }))
            ->orderByRaw('tanggal IS NULL')
            ->orderBy('tanggal')
            ->orderBy('jam')
            ->orderBy('round_id')
            ->orderBy('nomor_pertandingan')
            ->get();

        $adaFilter = $roundId || $groupId || $tanggal || $status || $cari !== '';

        return view('admin.manage.matches.index', compact('tournament', 'rounds', 'groups', 'matches', 'roundId', 'groupId', 'tanggal', 'status', 'cari', 'adaFilter'));
    }

    public function form(Tournament $tournament, ?MatchGame $match = null): View
    {
        if ($match && $match->exists) {
            $this->ensureOwned($match, $tournament);
            $match->load('events.player', 'events.assistPlayer');
        }

        $rounds = $tournament->rounds()->with('groups')->orderBy('nomor')->get();
        $teams = $tournament->teams()->orderBy('nama')->get();
        $referees = $tournament->referees()->orderBy('nama')->get();
        $players = $tournament->players()->orderBy('nama')->get();

        return view('admin.manage.matches.form', compact('tournament', 'match', 'rounds', 'teams', 'referees', 'players'));
    }

    public function store(Request $request, Tournament $tournament, PertandinganService $service): RedirectResponse
    {
        $validated = $this->validated($request, $tournament);
        $match = $tournament->matches()->create($validated);

        $service->hitungUlangSkor($match);

        return redirect()
            ->route('admin.manage.matches.index', $tournament)
            ->with('success', 'Pertandingan berhasil dijadwalkan.');
    }

    public function update(Request $request, Tournament $tournament, MatchGame $match, PertandinganService $service): RedirectResponse
    {
        $this->ensureOwned($match, $tournament);

        $validated = $this->validated($request, $tournament);
        $match->update($validated);

        if ($match->isSelesai()) {
            $service->tentukanPemenang($match, throwIfUnclear: false);
        }

        return redirect()
            ->route('admin.manage.matches.edit', [$tournament, $match])
            ->with('success', 'Detail pertandingan berhasil diperbarui.');
    }

    public function destroy(Tournament $tournament, MatchGame $match): RedirectResponse
    {
        $this->ensureOwned($match, $tournament);
        $match->delete();

        return redirect()
            ->route('admin.manage.matches.index', $tournament)
            ->with('success', 'Pertandingan berhasil dihapus.');
    }

    public function aturSkor(Request $request, Tournament $tournament, MatchGame $match, PertandinganService $service): RedirectResponse
    {
        $this->ensureOwned($match, $tournament);

        $validated = $request->validate([
            'skor_a' => 'required|integer|min:0|max:99',
            'skor_b' => 'required|integer|min:0|max:99',
            'skor_penalti_a' => 'nullable|integer|min:0|max:99',
            'skor_penalti_b' => 'nullable|integer|min:0|max:99',
        ]);

        if ($match->isGugur()) {
            $match->forceFill([
                'skor_penalti_a' => $validated['skor_penalti_a'] ?? null,
                'skor_penalti_b' => $validated['skor_penalti_b'] ?? null,
            ])->saveQuietly();
        }

        $service->aturSkor($match, (int) $validated['skor_a'], (int) $validated['skor_b']);

        return back()->with('success', 'Skor berhasil disimpan.');
    }

    public function ubahStatus(Request $request, Tournament $tournament, MatchGame $match, PertandinganService $service): RedirectResponse
    {
        $this->ensureOwned($match, $tournament);

        $validated = $request->validate([
            'status' => 'required|in:dijadwalkan,berlangsung,selesai,ditunda,dibatalkan',
        ]);

        $service->ubahStatus($match, $validated['status']);

        return back()->with('success', 'Status pertandingan diperbarui.');
    }

    protected function validated(Request $request, Tournament $tournament): array
    {
        return $request->validate([
            'round_id' => ['required', Rule::exists('rounds', 'id')->where('tournament_id', $tournament->id)],
            'group_id' => ['nullable', Rule::exists('groups', 'id')->where('tournament_id', $tournament->id)],
            'tim_a_id' => ['required', Rule::exists('teams', 'id')->where('tournament_id', $tournament->id)],
            'tim_b_id' => ['required', 'different:tim_a_id', Rule::exists('teams', 'id')->where('tournament_id', $tournament->id)],
            'wasit_id' => ['nullable', Rule::exists('referees', 'id')->where('tournament_id', $tournament->id)],
            'nomor_pertandingan' => 'nullable|integer|min:1|max:999',
            'babak' => 'nullable|string|max:100',
            'lokasi' => 'nullable|string|max:255',
            'tanggal' => 'nullable|date',
            'jam' => 'nullable|date_format:H:i',
            'catatan' => 'nullable|string',
            'status' => 'required|in:dijadwalkan,berlangsung,selesai,ditunda,dibatalkan',
        ]);
    }
}
