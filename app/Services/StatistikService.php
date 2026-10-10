<?php

namespace App\Services;

use App\Models\MatchEvent;
use App\Models\Tournament;
use Illuminate\Support\Collection;

class StatistikService
{
    public function topSkor(Tournament $tournament, ?int $roundId = null): Collection
    {
        return $this->akumulasi($tournament, 'gol', 'player_id', 'player', $roundId);
    }

    public function topAssist(Tournament $tournament, ?int $roundId = null): Collection
    {
        return $this->akumulasi($tournament, 'gol', 'assist_player_id', 'assistPlayer', $roundId);
    }

    public function kartuKuning(Tournament $tournament, ?int $roundId = null): Collection
    {
        return $this->akumulasi($tournament, 'kartu_kuning', 'player_id', 'player', $roundId);
    }

    public function kartuMerah(Tournament $tournament, ?int $roundId = null): Collection
    {
        return $this->akumulasi($tournament, 'kartu_merah', 'player_id', 'player', $roundId);
    }

    protected function akumulasi(Tournament $tournament, string $tipe, string $kolom, string $relasi, ?int $roundId): Collection
    {
        return MatchEvent::query()
            ->where('tournament_id', $tournament->id)
            ->where('tipe', $tipe)
            ->whereNotNull($kolom)
            ->whereHas('match', function ($query) use ($roundId) {
                $query->where('status', 'selesai')
                    ->when($roundId, fn ($q) => $q->where('round_id', $roundId));
            })
            ->selectRaw("{$kolom}, team_id, COUNT(*) as jumlah")
            ->groupBy($kolom, 'team_id')
            ->orderByDesc('jumlah')
            ->with([$relasi, 'team'])
            ->limit(50)
            ->get();
    }
}
