<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Round;

class KlasemenService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function klasemenGrup(Group $group): array
    {
        $format = $group->tournament->format();

        $rows = [];
        foreach ($group->teams()->orderBy('nama')->get() as $team) {
            $rows[$team->id] = [
                'tim_id' => $team->id,
                'nama' => $team->nama,
                'singkatan' => $team->singkatan,
                'main' => 0,
                'menang' => 0,
                'seri' => 0,
                'kalah' => 0,
                'gm' => 0,
                'gk' => 0,
                'sg' => 0,
                'poin' => 0,
                'seri_penuh' => false,
            ];
        }

        $matches = $group->matches()->where('status', 'selesai')->get();

        foreach ($matches as $match) {
            $a = $match->tim_a_id;
            $b = $match->tim_b_id;

            if (! isset($rows[$a], $rows[$b])) {
                continue;
            }

            $sa = (int) $match->skor_a;
            $sb = $match->skor_b;

            $rows[$a]['main']++;
            $rows[$b]['main']++;
            $rows[$a]['gm'] += $sa;
            $rows[$a]['gk'] += $sb;
            $rows[$b]['gm'] += $sb;
            $rows[$b]['gk'] += $sa;

            if ($sa > $sb) {
                $rows[$a]['menang']++;
                $rows[$b]['kalah']++;
                $rows[$a]['poin'] += (int) $format['poin_menang'];
                $rows[$b]['poin'] += (int) $format['poin_kalah'];
            } elseif ($sa < $sb) {
                $rows[$b]['menang']++;
                $rows[$a]['kalah']++;
                $rows[$b]['poin'] += (int) $format['poin_menang'];
                $rows[$a]['poin'] += (int) $format['poin_kalah'];
            } else {
                $rows[$a]['seri']++;
                $rows[$b]['seri']++;
                $rows[$a]['poin'] += (int) $format['poin_seri'];
                $rows[$b]['poin'] += (int) $format['poin_seri'];
            }
        }

        $rows = array_values($rows);

        foreach ($rows as &$row) {
            $row['sg'] = $row['gm'] - $row['gk'];
        }
        unset($row);

        usort($rows, function ($a, $b) {
            return [$b['poin'], $b['sg'], $b['gm']] <=> [$a['poin'], $a['sg'], $a['gm']];
        });

        $peringkat = 1;
        foreach ($rows as $i => &$row) {
            $row['peringkat'] = $peringkat++;
            $prev = $rows[$i - 1] ?? null;
            $next = $rows[$i + 1] ?? null;

            $samaDengan = function ($other) use ($row) {
                return $other
                    && $other['poin'] === $row['poin']
                    && $other['sg'] === $row['sg']
                    && $other['gm'] === $row['gm'];
            };

            $row['seri_penuh'] = $samaDengan($prev) || $samaDengan($next);
        }
        unset($row);

        return $rows;
    }

    public function klasemenRound(Round $round): array
    {
        return $round->groups->mapWithKeys(function ($group) {
            return [$group->id => [
                'grup' => $group,
                'klasemen' => $this->klasemenGrup($group),
            ]];
        })->all();
    }
}
