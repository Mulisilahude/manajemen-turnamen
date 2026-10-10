<?php

namespace App\Http\Controllers\Admin\Manage;

use App\Enums\EventType;
use App\Http\Controllers\Admin\Concerns\ResolvesTournament;
use App\Http\Controllers\Controller;
use App\Models\MatchEvent;
use App\Models\MatchGame;
use App\Models\Tournament;
use App\Services\AuditService;
use App\Services\PertandinganService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class MatchEventController extends Controller
{
    use ResolvesTournament;

    public function store(Request $request, Tournament $tournament, MatchGame $match, PertandinganService $service): RedirectResponse
    {
        $this->ensureOwned($match, $tournament);

        $validated = $request->validate([
            'tipe' => ['required', new Enum(EventType::class)],
            'team_id' => ['required', Rule::exists('teams', 'id')->where('tournament_id', $tournament->id)],
            'player_id' => ['nullable', Rule::exists('players', 'id')->where('tournament_id', $tournament->id)],
            'assist_player_id' => ['nullable', Rule::exists('players', 'id')->where('tournament_id', $tournament->id)],
            'menit' => 'nullable|integer|min:0|max:200',
            'detail' => 'nullable|string|max:255',
        ]);

        $validated['tournament_id'] = $tournament->id;
        $match->events()->create($validated);
        $service->hitungUlangSkor($match);

        if ($match->isSelesai()) {
            $service->tentukanPemenang($match, throwIfUnclear: false);
        }

        return back()->with('success', 'Kejadian berhasil dicatat.');
    }

    public function destroy(Tournament $tournament, MatchGame $match, MatchEvent $event, PertandinganService $service, AuditService $audit): RedirectResponse
    {
        $this->ensureOwned($match, $tournament);
        abort_unless((int) $event->match_id === (int) $match->id, 404);

        $match->events()->whereKey($event->id)->delete();
        $service->hitungUlangSkor($match);

        if ($match->isSelesai()) {
            $service->tentukanPemenang($match, throwIfUnclear: false);
        }

        return back()->with('success', 'Kejadian berhasil dihapus.');
    }
}
