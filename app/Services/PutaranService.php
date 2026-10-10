<?php

namespace App\Services;

use App\Enums\PhaseType;
use App\Models\Group;
use App\Models\MatchGame;
use App\Models\Round;
use App\Models\Team;
use App\Models\Tournament;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PutaranService
{
    public function __construct(private AuditService $audit) {}

    public function buatPutaran(Tournament $tournament, string $nama, PhaseType $tipe, int $jumlahGrup = 0): Round
    {
        return DB::transaction(function () use ($tournament, $nama, $tipe, $jumlahGrup) {
            $nomor = (int) $tournament->rounds()->max('nomor') + 1;

            /** @var Round $round */
            $round = $tournament->rounds()->create([
                'nomor' => $nomor,
                'nama' => $nama,
                'tipe' => $tipe->value,
            ]);

            for ($i = 0; $i < $jumlahGrup; $i++) {
                $round->groups()->create([
                    'tournament_id' => $tournament->id,
                    'nama' => 'Grup '.chr(65 + $i),
                    'urutan' => $i + 1,
                ]);
            }

            $this->audit->log($tournament, 'buat_putaran', 'round', $round->id, 'Membuat '.$nama);

            return $round->load('groups');
        });
    }

    public function buatGrup(Round $round, string $nama): Group
    {
        $urutan = (int) $round->groups()->max('urutan') + 1;

        return $round->groups()->create([
            'tournament_id' => $round->tournament_id,
            'nama' => $nama,
            'urutan' => $urutan,
        ]);
    }

    public function isiGrup(Group $group, array $teamIds): void
    {
        $tournament = $group->tournament;
        $round = $group->round;

        $teamIds = array_values(array_unique(array_map('intval', $teamIds)));

        $valid = Team::where('tournament_id', $tournament->id)->whereIn('id', $teamIds)->count();
        if ($valid !== count($teamIds)) {
            throw ValidationException::withMessages(['tim' => 'Ada tim yang bukan milik turnamen ini.']);
        }

        $timDiRoundLain = DB::table('group_team')
            ->join('groups', 'groups.id', '=', 'group_team.group_id')
            ->where('groups.round_id', $round->id)
            ->where('groups.id', '!=', $group->id)
            ->whereIn('group_team.team_id', $teamIds)
            ->pluck('group_team.team_id');

        if ($timDiRoundLain->isNotEmpty()) {
            throw ValidationException::withMessages(['tim' => 'Ada tim yang sudah berada di grup lain pada putaran ini.']);
        }

        $group->teams()->sync($teamIds);
    }

    public function roundTerkunci(Round $round): bool
    {
        return $round->matches()->exists();
    }

    /**
     * Menyimpan pembagian grup manual sekaligus (semua grup pada satu putaran).
     *
     * @param  array<int|string, array<int, int|string>>  $perGrup  [group_id => [team_id, ...]]
     */
    public function simpanPenempatanGrup(Round $round, array $perGrup): void
    {
        if ($round->matches()->exists()) {
            throw ValidationException::withMessages(['grup' => 'Grup tidak dapat diubah karena putaran ini sudah memiliki jadwal atau hasil pertandingan.']);
        }

        $tournament = $round->tournament;
        $perGrup = array_combine(array_map('intval', array_keys($perGrup)), array_values($perGrup));
        $groupIds = $round->groups()->pluck('id')->map(fn ($v) => (int) $v)->all();

        $semua = [];
        foreach ($perGrup as $gid => $ids) {
            if (! in_array($gid, $groupIds, true)) {
                throw ValidationException::withMessages(['grup' => 'Grup tidak dikenal pada putaran ini.']);
            }
            foreach ((array) $ids as $id) {
                $semua[] = (int) $id;
            }
        }

        if (count($semua) !== count(array_unique($semua))) {
            throw ValidationException::withMessages(['grup' => 'Satu tim tidak boleh berada di dua grup pada putaran yang sama.']);
        }

        if (! empty($semua)) {
            $valid = Team::where('tournament_id', $tournament->id)->whereIn('id', $semua)->count();
            if ($valid !== count(array_unique($semua))) {
                throw ValidationException::withMessages(['grup' => 'Ada tim yang bukan milik turnamen ini.']);
            }
        }

        DB::transaction(function () use ($round, $perGrup) {
            foreach ($round->groups as $group) {
                $group->teams()->sync(array_map('intval', (array) ($perGrup[$group->id] ?? [])));
            }
        });

        $this->audit->log($tournament, 'atur_grup', 'round', $round->id, 'Mengatur pembagian grup manual');
    }

    /**
     * Membuat draf undian: membagi seluruh tim ke sejumlah grup secara acak.
     *
     * @return array<int, array{nama: string, urutan: int, tim_ids: array<int, int>}>
     */
    public function generateUndian(Round $round, int $jumlahGrup): array
    {
        $tournament = $round->tournament;
        $teams = $tournament->teams()->orderBy('nama')->pluck('id')->map(fn ($v) => (int) $v)->all();
        $total = count($teams);

        if ($jumlahGrup < 2) {
            throw ValidationException::withMessages(['jumlah_grup' => 'Minimal dua grup diperlukan untuk undian.']);
        }

        if ($jumlahGrup > $total) {
            throw ValidationException::withMessages(['jumlah_grup' => 'Jumlah grup tidak boleh melebihi jumlah tim yang tersedia.']);
        }

        shuffle($teams);

        $base = intdiv($total, $jumlahGrup);
        $sisa = $total % $jumlahGrup;

        $groups = [];
        $idx = 0;
        for ($i = 0; $i < $jumlahGrup; $i++) {
            $kapasitas = $base + ($i < $sisa ? 1 : 0);
            $timIds = [];
            for ($j = 0; $j < $kapasitas; $j++) {
                $timIds[] = $teams[$idx++];
            }
            $groups[] = ['nama' => 'Grup '.chr(65 + $i), 'urutan' => $i + 1, 'tim_ids' => $timIds];
        }

        return $groups;
    }

    /**
     * Menyahkan draf undian ke database.
     *
     * @param  array<int, array{nama?: string, urutan?: int, tim_ids?: array<int, int>>}  $groups
     */
    public function sahkanUndian(Round $round, array $groups): void
    {
        if ($round->matches()->exists()) {
            throw ValidationException::withMessages(['grup' => 'Undian tidak dapat disahkan karena putaran ini sudah memiliki jadwal atau hasil pertandingan.']);
        }

        $tournament = $round->tournament;

        DB::transaction(function () use ($round, $tournament, $groups) {
            $perGrup = [];

            foreach (array_values($groups) as $i => $grup) {
                $nama = $grup['nama'] ?? ('Grup '.chr(65 + $i));
                $group = $round->groups()->where('nama', $nama)->first();

                if (! $group) {
                    $group = $round->groups()->create([
                        'tournament_id' => $tournament->id,
                        'nama' => $nama,
                        'urutan' => $grup['urutan'] ?? ($i + 1),
                    ]);
                }

                $perGrup[$group->id] = array_map('intval', (array) ($grup['tim_ids'] ?? []));
            }

            foreach ($round->groups as $group) {
                $group->teams()->sync($perGrup[$group->id] ?? []);
            }
        });

        $this->audit->log($tournament, 'undian_grup', 'round', $round->id, 'Mengundi pembagian grup fase grup');
    }

    public function siapkanGrupLanjutan(Round $current, array $groups): Round
    {
        return DB::transaction(function () use ($current, $groups) {
            $tournament = $current->tournament;

            $this->pastikanBelumAdaLanjutan($current, $tournament);

            if (count($groups) < 2) {
                throw ValidationException::withMessages(['grup' => 'Minimal dua grup lanjutan.']);
            }

            $semuaTim = [];
            foreach ($groups as $grup) {
                $tim = array_map('intval', $grup['teams'] ?? []);
                if (count(array_unique($tim)) !== 4 || count($tim) !== 4) {
                    throw ValidationException::withMessages(['grup' => 'Setiap grup lanjutan harus berisi tepat 4 tim.']);
                }
                $semuaTim = array_merge($semuaTim, $tim);
            }

            if (count($semuaTim) !== count(array_unique($semuaTim))) {
                throw ValidationException::withMessages(['grup' => 'Satu tim tidak boleh berada di dua grup.']);
            }

            $this->pastikanTimMilikTurnamen($tournament, $semuaTim);

            $round = $this->buatPutaran($tournament, $groups[0]['nama_putaran'] ?? ('Putaran '.($current->nomor + 1)), PhaseType::GRUP);
            $round->forceFill(['parent_round_id' => $current->id])->save();

            foreach (array_values($groups) as $i => $grup) {
                $newGroup = $round->groups()->create([
                    'tournament_id' => $tournament->id,
                    'nama' => $grup['nama'] ?? ('Grup '.chr(65 + $i)),
                    'urutan' => $i + 1,
                ]);
                $newGroup->teams()->sync(array_map('intval', $grup['teams']));
            }

            $current->forceFill(['lanjutan_dibuat' => true])->save();

            $this->audit->log($tournament, 'lanjut_grup', 'round', $round->id, 'Membuat grup lanjutan dari '.$current->nama);

            return $round->fresh(['groups.teams']);
        });
    }

    public function siapkanFaseGugur(Round $current, array $pairs): Round
    {
        return DB::transaction(function () use ($current, $pairs) {
            $tournament = $current->tournament;

            $this->pastikanBelumAdaLanjutan($current, $tournament);

            $semuaTim = [];
            foreach ($pairs as $pair) {
                $a = (int) ($pair['tim_a'] ?? 0);
                $b = (int) ($pair['tim_b'] ?? 0);
                if ($a <= 0 || $b <= 0) {
                    throw ValidationException::withMessages(['pasangan' => 'Setiap pertandingan harus memiliki dua tim.']);
                }
                $semuaTim[] = $a;
                $semuaTim[] = $b;
            }

            if (count($semuaTim) !== count(array_unique($semuaTim))) {
                throw ValidationException::withMessages(['pasangan' => 'Satu tim tidak boleh muncul dua kali.']);
            }

            $this->pastikanTimMilikTurnamen($tournament, $semuaTim);

            $babak = $this->labelBabak(count($pairs));
            $round = $this->buatPutaran($tournament, $babak, PhaseType::GUGUR);
            $round->forceFill(['parent_round_id' => $current->id])->save();

            foreach (array_values($pairs) as $i => $pair) {
                MatchGame::create([
                    'tournament_id' => $tournament->id,
                    'round_id' => $round->id,
                    'nomor_pertandingan' => $i + 1,
                    'fase' => PhaseType::GUGUR->value,
                    'babak' => $babak,
                    'tim_a_id' => (int) $pair['tim_a'],
                    'tim_b_id' => (int) $pair['tim_b'],
                    'status' => 'dijadwalkan',
                ]);
            }

            $current->forceFill(['lanjutan_dibuat' => true])->save();

            $this->audit->log($tournament, 'lanjut_gugur', 'round', $round->id, 'Membuat '.$babak.' dari '.$current->nama);

            return $round->fresh(['matches.teamA', 'matches.teamB']);
        });
    }

    public function juara(Tournament $tournament): ?Team
    {
        $final = $this->finalMatch($tournament);

        return $final?->winner;
    }

    public function finalMatch(Tournament $tournament): ?MatchGame
    {
        return $tournament->matches()
            ->where('fase', PhaseType::GUGUR)
            ->where('babak', 'Final')
            ->where('status', 'selesai')
            ->whereNotNull('pemenang_id')
            ->latest('id')
            ->first();
    }

    protected function labelBabak(int $jumlahPertandingan): string
    {
        return match ($jumlahPertandingan) {
            1 => 'Final',
            2 => 'Semifinal',
            4 => 'Perempat Final',
            8 => 'Babak 16 Besar',
            default => 'Babak '.($jumlahPertandingan * 2),
        };
    }

    protected function pastikanBelumAdaLanjutan(Round $current, Tournament $tournament): void
    {
        if ($current->lanjutan_dibuat || $tournament->rounds()->where('parent_round_id', $current->id)->exists()) {
            throw ValidationException::withMessages(['putaran' => 'Putaran berikutnya sudah pernah dibuat.']);
        }
    }

    protected function pastikanTimMilikTurnamen(Tournament $tournament, array $teamIds): void
    {
        $valid = Team::where('tournament_id', $tournament->id)->whereIn('id', $teamIds)->count();
        if ($valid !== count(array_unique($teamIds))) {
            throw ValidationException::withMessages(['tim' => 'Ada tim yang bukan milik turnamen ini.']);
        }
    }
}
