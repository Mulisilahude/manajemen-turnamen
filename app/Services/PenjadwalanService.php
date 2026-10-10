<?php

namespace App\Services;

use App\Enums\PhaseType;
use App\Models\MatchGame;
use App\Models\Round;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PenjadwalanService
{
    public function __construct(private AuditService $audit) {}

    public function roundTerkunci(Round $round): bool
    {
        return $round->matches()->whereIn('status', ['berlangsung', 'selesai'])->exists();
    }

    public function roundPunyaPertandingan(Round $round): bool
    {
        return $round->matches()->exists();
    }

    /**
     * Membuat draf jadwal round-robin per grup tanpa menyimpan ke database.
     *
     * @return array{rows: array<int, array<string, mixed>>, errors: array<int, string>, config: array<string, mixed>}
     */
    public function generateJadwal(Round $round, array $config): array
    {
        $config = array_merge([
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => null,
            'jam_mulai' => '08:00',
            'jam_selesai' => '17:00',
            'durasi' => 60,
            'jeda' => 15,
            'lapangan' => [],
            'hari' => [],
            'dua_putaran' => false,
        ], $config);

        $rows = [];
        foreach ($round->groups()->with('teams')->get() as $group) {
            $teamIds = $group->teams->pluck('id')->map(fn ($v) => (int) $v)->all();

            if (count($teamIds) < 2) {
                continue;
            }

            foreach ($this->pasangan($teamIds, (bool) $config['dua_putaran']) as $pair) {
                $rows[] = [
                    'round_id' => $round->id,
                    'group_id' => $group->id,
                    'tim_a_id' => $pair[0],
                    'tim_b_id' => $pair[1],
                    'tanggal' => null,
                    'jam' => null,
                    'lokasi' => null,
                    'referee_id' => null,
                    'status' => 'dijadwalkan',
                ];
            }
        }

        $errors = $this->tempelkanSlot($rows, $config);

        return ['rows' => $rows, 'errors' => $errors, 'config' => $config];
    }

    /**
     * Menempatkan setiap baris pada slot tanggal/jam/lapangan tanpa bentrok tim.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<string, mixed>  $config
     * @return array<int, string>
     */
    protected function tempelkanSlot(array &$rows, array $config): array
    {
        $dates = $this->rentangTanggal($config);
        if (empty($dates)) {
            return ['Tidak ada tanggal tersedia pada rentang yang dipilih.'];
        }

        $slots = $this->slotWaktu($config);
        if (empty($slots)) {
            return ['Tidak ada slot waktu valid. Periksa jam mulai, jam selesai, durasi, dan jeda.'];
        }

        $lapangan = array_values(array_filter((array) $config['lapangan'], fn ($v) => $v !== null && $v !== ''));
        if (empty($lapangan)) {
            return ['Minimal satu lapangan diperlukan.'];
        }

        $occupied = [];
        $teamBusy = [];
        $errors = [];

        foreach ($rows as &$row) {
            $assigned = false;

            foreach ($dates as $date) {
                foreach ($slots as $jam) {
                    if (isset($teamBusy["{$date}|{$jam}|{$row['tim_a_id']}"]) || isset($teamBusy["{$date}|{$jam}|{$row['tim_b_id']}"])) {
                        continue;
                    }

                    foreach ($lapangan as $lap) {
                        if (isset($occupied["{$date}|{$jam}|{$lap}"])) {
                            continue;
                        }

                        $row['tanggal'] = $date;
                        $row['jam'] = $jam;
                        $row['lokasi'] = $lap;
                        $occupied["{$date}|{$jam}|{$lap}"] = true;
                        $teamBusy["{$date}|{$jam}|{$row['tim_a_id']}"] = true;
                        $teamBusy["{$date}|{$jam}|{$row['tim_b_id']}"] = true;

                        break 3;
                    }
                }
            }

            if (empty($row['tanggal'])) {
                $errors[] = 'Tidak cukup slot untuk pertandingan tim #'.$row['tim_a_id'].' vs #'.$row['tim_b_id'].'. Tambah tanggal, lapangan, atau perpanjang jam.';
            }
        }
        unset($row);

        return $errors;
    }

    /**
     * Memeriksa bentrok tim, lapangan, dan wasit pada sekumpulan baris jadwal.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<int, int>  $kecualiIds  id pertandingan yang sedang diperbarui (tidak dianggap bentrok dengan dirinya)
     * @return array<int, string>
     */
    public function validasiBentrok(int $tournamentId, array $rows, array $kecualiIds = []): array
    {
        $occupancy = [];
        $errors = [];

        $existing = MatchGame::query()
            ->where('tournament_id', $tournamentId)
            ->whereNotNull('tanggal')
            ->whereNotNull('jam')
            ->when($kecualiIds, fn ($q) => $q->whereNotIn('id', $kecualiIds))
            ->get(['id', 'tanggal', 'jam', 'lokasi', 'wasit_id', 'tim_a_id', 'tim_b_id']);

        foreach ($existing as $match) {
            $this->tandai($occupancy, $match->tanggal, $match->jam, $match->lokasi, $match->wasit_id, $match->tim_a_id, $match->tim_b_id);
        }

        foreach ($rows as $row) {
            if (empty($row['tanggal']) || empty($row['jam'])) {
                continue;
            }

            $tanggal = $row['tanggal'] instanceof \DateTimeInterface
                ? $row['tanggal']->format('Y-m-d')
                : (string) $row['tanggal'];
            $jam = substr((string) $row['jam'], 0, 5);

            $this->periksa($occupancy, $errors, $tanggal, $jam, $row['lokasi'] ?? null, $row['referee_id'] ?? null, $row['tim_a_id'] ?? null, $row['tim_b_id'] ?? null);
            $this->tandai($occupancy, $tanggal, $jam, $row['lokasi'] ?? null, $row['referee_id'] ?? null, $row['tim_a_id'] ?? null, $row['tim_b_id'] ?? null);
        }

        return array_values(array_unique($errors));
    }

    /**
     * Menyimpan draf jadwal yang sudah disahkan ke database.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function simpanJadwal(Round $round, array $rows): int
    {
        if ($this->roundTerkunci($round)) {
            throw ValidationException::withMessages(['jadwal' => 'Putaran ini memiliki pertandingan yang sedang berlangsung atau selesai dan tidak dapat dijadwalkan ulang.']);
        }

        $tournament = $round->tournament;

        $rows = array_values(array_filter($rows, fn ($r) => ! empty($r['tim_a_id']) && ! empty($r['tim_b_id'])));

        if (empty($rows)) {
            throw ValidationException::withMessages(['jadwal' => 'Tidak ada pertandingan untuk disimpan.']);
        }

        foreach ($rows as $row) {
            if ((int) $row['tim_a_id'] === (int) $row['tim_b_id']) {
                throw ValidationException::withMessages(['jadwal' => 'Sebuah pertandingan tidak boleh mempertemukan tim yang sama.']);
            }
        }

        $errors = $this->validasiBentrok($tournament->id, $rows);
        if ($errors) {
            throw ValidationException::withMessages(['jadwal' => implode(' ', $errors)]);
        }

        $count = 0;
        DB::transaction(function () use ($round, $tournament, $rows, &$count) {
            foreach ($rows as $i => $row) {
                MatchGame::create([
                    'tournament_id' => $tournament->id,
                    'round_id' => $round->id,
                    'group_id' => $row['group_id'] ?? null,
                    'nomor_pertandingan' => $row['nomor_pertandingan'] ?? ($i + 1),
                    'fase' => $round->tipe === PhaseType::GUGUR ? PhaseType::GUGUR->value : PhaseType::GRUP->value,
                    'babak' => $round->nama,
                    'tim_a_id' => (int) $row['tim_a_id'],
                    'tim_b_id' => (int) $row['tim_b_id'],
                    'wasit_id' => ! empty($row['referee_id']) ? (int) $row['referee_id'] : null,
                    'tanggal' => ! empty($row['tanggal']) ? $row['tanggal'] : null,
                    'jam' => ! empty($row['jam']) ? substr((string) $row['jam'], 0, 5) : null,
                    'lokasi' => $row['lokasi'] ?? null,
                    'status' => 'dijadwalkan',
                ]);
                $count++;
            }
        });

        $this->audit->log($tournament, 'buat_jadwal', 'round', $round->id, "Membuat {$count} jadwal pertandingan untuk ".$round->nama);

        return $count;
    }

    /**
     * @param  array<int, int>  $ids
     * @return array<int, array{0: int, 1: int}>
     */
    protected function pasangan(array $ids, bool $duaPutaran): array
    {
        $ids = array_values($ids);
        $out = [];
        $n = count($ids);

        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                $out[] = [$ids[$i], $ids[$j]];
                if ($duaPutaran) {
                    $out[] = [$ids[$j], $ids[$i]];
                }
            }
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<int, string>
     */
    protected function rentangTanggal(array $config): array
    {
        $mulai = Carbon::parse($config['tanggal_mulai']);
        $selesai = ! empty($config['tanggal_selesai'])
            ? Carbon::parse($config['tanggal_selesai'])
            : $mulai->copy()->addDays(14);

        if ($selesai->lt($mulai)) {
            return [];
        }

        $hari = array_map('intval', (array) ($config['hari'] ?? []));
        $dates = [];

        for ($d = $mulai->copy(); $d->lte($selesai); $d->addDay()) {
            if (! empty($hari) && ! in_array($d->isoWeekday(), $hari, true)) {
                continue;
            }
            $dates[] = $d->format('Y-m-d');
        }

        return $dates;
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<int, string>
     */
    protected function slotWaktu(array $config): array
    {
        $durasi = max(1, (int) $config['durasi']);
        $jeda = max(0, (int) $config['jeda']);
        $langkah = $durasi + $jeda;

        $mulai = Carbon::parse($config['jam_mulai']);
        $selesai = Carbon::parse($config['jam_selesai']);

        $slots = [];
        for ($t = $mulai->copy(); $t->copy()->addMinutes($durasi)->lte($selesai); $t->addMinutes($langkah)) {
            $slots[] = $t->format('H:i');
        }

        return $slots;
    }

    /**
     * @param  array<string, bool>  $occupancy
     */
    protected function tandai(array &$occupancy, mixed $tanggal, mixed $jam, mixed $lokasi, mixed $wasit, mixed $timA, mixed $timB): void
    {
        if (empty($tanggal) || empty($jam)) {
            return;
        }

        $tanggal = $tanggal instanceof \DateTimeInterface ? $tanggal->format('Y-m-d') : (string) $tanggal;
        $jam = substr((string) $jam, 0, 5);
        $key = $tanggal.'|'.$jam;

        if (! empty($lokasi)) {
            $occupancy[$key.'|lapangan|'.$lokasi] = true;
        }
        if (! empty($wasit)) {
            $occupancy[$key.'|wasit|'.$wasit] = true;
        }
        if (! empty($timA)) {
            $occupancy[$key.'|tim|'.$timA] = true;
        }
        if (! empty($timB)) {
            $occupancy[$key.'|tim|'.$timB] = true;
        }
    }

    /**
     * @param  array<string, bool>  $occupancy
     * @param  array<int, string>  $errors
     */
    protected function periksa(array $occupancy, array &$errors, string $tanggal, string $jam, mixed $lokasi, mixed $wasit, mixed $timA, mixed $timB): void
    {
        $key = $tanggal.'|'.$jam;
        $waktu = Carbon::parse($tanggal)->translatedFormat('d M Y').' '.$jam;

        if (! empty($lokasi) && isset($occupancy[$key.'|lapangan|'.$lokasi])) {
            $errors[] = "Bentrok lapangan \"{$lokasi}\" pada {$waktu}.";
        }
        if (! empty($wasit) && isset($occupancy[$key.'|wasit|'.$wasit])) {
            $errors[] = "Bentrok wasit pada {$waktu}.";
        }
        if (! empty($timA) && isset($occupancy[$key.'|tim|'.$timA])) {
            $errors[] = "Bentrok jadwal tim #{$timA} pada {$waktu}.";
        }
        if (! empty($timB) && isset($occupancy[$key.'|tim|'.$timB])) {
            $errors[] = "Bentrok jadwal tim #{$timB} pada {$waktu}.";
        }
    }
}
