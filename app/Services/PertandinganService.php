<?php

namespace App\Services;

use App\Enums\MatchStatus;
use App\Models\MatchGame;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PertandinganService
{
    public function __construct(private AuditService $audit) {}

    public function hitungUlangSkor(MatchGame $match): void
    {
        $skorA = $match->events()->where('tipe', 'gol')->where('team_id', $match->tim_a_id)->count();
        $skorB = $match->events()->where('tipe', 'gol')->where('team_id', $match->tim_b_id)->count();

        $match->forceFill(['skor_a' => $skorA, 'skor_b' => $skorB])->saveQuietly();
    }

    public function aturSkor(MatchGame $match, int $targetA, int $targetB): void
    {
        DB::transaction(function () use ($match, $targetA, $targetB) {
            $this->selaraskan($match, $match->tim_a_id, $targetA);
            $this->selaraskan($match, $match->tim_b_id, $targetB);
            $this->hitungUlangSkor($match);
        });

        if ($match->skor_a !== $targetA || $match->skor_b !== $targetB) {
            throw ValidationException::withMessages([
                'skor' => 'Skor tidak dapat disesuaikan karena ada kejadian gol detail. Hapus kejadian gol terlebih dahulu.',
            ]);
        }

        if ($match->isSelesai()) {
            $this->tentukanPemenang($match, throwIfUnclear: false);
        }
    }

    protected function selaraskan(MatchGame $match, ?int $teamId, int $target): void
    {
        if (! $teamId) {
            if ($target > 0) {
                throw ValidationException::withMessages(['skor' => 'Pertandingan belum memiliki tim yang lengkap.']);
            }

            return;
        }

        $events = $match->events()->where('tipe', 'gol')->where('team_id', $teamId)->get();
        $current = $events->count();

        if ($current < $target) {
            for ($i = $current; $i < $target; $i++) {
                $match->events()->create([
                    'tournament_id' => $match->tournament_id,
                    'team_id' => $teamId,
                    'tipe' => 'gol',
                    'detail' => 'skor_cepat',
                ]);
            }

            return;
        }

        if ($current > $target) {
            $selisih = $current - $target;
            $dapatDihapus = $match->events()
                ->where('tipe', 'gol')
                ->where('team_id', $teamId)
                ->whereNull('player_id')
                ->latest('id')
                ->limit($selisih)
                ->get();

            if ($dapatDihapus->count() < $selisih) {
                throw ValidationException::withMessages([
                    'skor' => 'Skor lebih kecil dari jumlah gol detail yang tercatat. Hapus kejadian gol terlebih dahulu.',
                ]);
            }

            foreach ($dapatDihapus as $event) {
                $event->delete();
            }
        }
    }

    public function ubahStatus(MatchGame $match, string $status): void
    {
        $match->status = MatchStatus::from($status);

        if ($match->status === MatchStatus::SELESAI) {
            $this->tentukanPemenang($match, throwIfUnclear: false);
        } else {
            $match->pemenang_id = null;
            $match->saveQuietly();
        }
    }

    public function tentukanPemenang(MatchGame $match, bool $throwIfUnclear = true): ?int
    {
        $winner = null;

        if ($match->skor_a > $match->skor_b) {
            $winner = $match->tim_a_id;
        } elseif ($match->skor_b > $match->skor_a) {
            $winner = $match->tim_b_id;
        } elseif ($match->isGugur() && $match->skor_penalti_a !== null && $match->skor_penalti_b !== null) {
            if ($match->skor_penalti_a > $match->skor_penalti_b) {
                $winner = $match->tim_a_id;
            } elseif ($match->skor_penalti_b > $match->skor_penalti_a) {
                $winner = $match->tim_b_id;
            }
        }

        if ($winner === null && $match->isGugur() && $throwIfUnclear) {
            throw ValidationException::withMessages([
                'pemenang' => 'Pemenang belum jelas. Atur skor adu penalti sesuai aturan turnamen.',
            ]);
        }

        $match->forceFill(['pemenang_id' => $winner])->saveQuietly();

        return $winner;
    }
}
