<?php

namespace App\Services;

use App\Enums\MatchStatus;
use App\Enums\PhaseType;
use App\Models\Round;
use App\Models\Team;
use App\Models\Tournament;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Impor data resmi panitia (pembagian grup + jadwal) yang sudah ditetapkan.
 *
 * Prinsip:
 * - Tidak mengundi atau menjadwalkan otomatis. Data berasal dari teks resmi.
 * - Tidak menggabungkan tim yang namanya nyaris sama; ditandai untuk admin.
 * - Tidak menimpa pertandingan yang sudah punya skor/kejadian.
 * - Aman dari klik ganda (idempoten berdasarkan kode/pasangan pertandingan).
 */
class OfficialImportService
{
    public function __construct(private AuditService $audit) {}

    public const BULAN = [
        'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4,
        'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8,
        'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
    ];

    /**
     * Menguraikan teks pembagian grup menjadi struktur grup + tim.
     *
     * @return array{grup: array<int, array{nama: string, kode: string, tim: array<int, string>}>, catatan: array<int, string>}
     */
    public function parseGrup(string $teks): array
    {
        $grup = [];
        $catatan = [];
        $index = [];
        $aktif = null;

        foreach (preg_split('/\r\n|\r|\n/', $teks) as $baris) {
            $baris = trim($baris);
            if ($baris === '') {
                continue;
            }

            if (preg_match('/^(?:grup\s+([A-Za-z])|([A-Za-z]))\s*(?:[:.\-–]\s*(.*))?$/iu', $baris, $m) && $baris !== '') {
                $kode = strtoupper(($m[1] ?? '') !== '' ? $m[1] : ($m[2] ?? ''));
                if (! isset($index[$kode])) {
                    $index[$kode] = count($grup);
                    $grup[] = ['nama' => 'Grup '.$kode, 'kode' => $kode, 'tim' => []];
                }
                $aktif = $index[$kode];
                $sisa = trim($m[3] ?? '');
                if ($sisa !== '') {
                    foreach ($this->pisahTim($sisa) as $tim) {
                        $grup[$aktif]['tim'][] = $tim;
                    }
                }

                continue;
            }

            if ($aktif === null) {
                continue;
            }

            foreach ($this->pisahTim($baris) as $tim) {
                $grup[$aktif]['tim'][] = $tim;
            }
        }

        foreach ($grup as $i => $g) {
            $grup[$i]['tim'] = array_values(array_filter($g['tim'], fn ($t) => $t !== ''));
            if (count($grup[$i]['tim']) !== 4) {
                $catatan[] = $g['nama'].' berisi '.count($grup[$i]['tim']).' tim (diharapkan 4).';
            }
        }

        return ['grup' => $grup, 'catatan' => $catatan];
    }

    /**
     * @return array<int, string>
     */
    protected function pisahTim(string $baris): array
    {
        $bagian = preg_split('/\s*[,;]\s*/u', $baris) ?: [];

        return array_values(array_filter(array_map('trim', $bagian), fn ($t) => $t !== ''));
    }

    /**
     * Format: Tanggal | Jam | Kode | Tim A | Tim B | Grup | Skor
     * Pemisah: "|", tab, atau ";".
     *
     * @return array{baris: array<int, array{tanggal: ?string, jam: ?string, kode: ?string, tim_a: string, tim_b: string, grup: ?string, skor: ?string}>, catatan: array<int, string>}
     */
    public function parseJadwal(string $teks): array
    {
        $baris = [];
        $catatan = [];

        foreach (preg_split('/\r\n|\r|\n/', $teks) as $no => $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $kolom = $this->pisahKolom($line);

            if ($this->barisHeader($kolom)) {
                continue;
            }

            $kolom = array_map('trim', $kolom);
            $kolom = array_pad($kolom, 7, null);

            [$tanggal, $jam, $kode, $timA, $timB, $grup, $skor] = $kolom;

            if (($timA ?? '') === '' || ($timB ?? '') === '') {
                $catatan[] = 'Baris '.($no + 1).' diabaikan: nama tim tidak lengkap ("'.$line.'").';

                continue;
            }

            $baris[] = [
                'tanggal' => $tanggal ?: null,
                'jam' => $jam ?: null,
                'kode' => $kode ?: null,
                'tim_a' => $timA,
                'tim_b' => $timB,
                'grup' => $grup ?: null,
                'skor' => $skor ?: null,
            ];
        }

        return ['baris' => $baris, 'catatan' => $catatan];
    }

    /**
     * @return array<int, string>
     */
    protected function pisahKolom(string $line): array
    {
        foreach (["\t", '|', ';'] as $pemisah) {
            if (str_contains($line, $pemisah)) {
                return explode($pemisah, $line);
            }
        }

        return preg_split('/\s{2,}/u', $line) ?: [$line];
    }

    /**
     * @param  array<int, ?string>  $kolom
     */
    protected function barisHeader(array $kolom): bool
    {
        $gabung = strtolower(implode(' ', array_map(fn ($k) => (string) $k, $kolom)));

        return str_contains($gabung, 'tanggal') || str_contains($gabung, 'tim a') || str_contains($gabung, 'tim b');
    }

    /**
     * @return array{grup: array, jadwal: array, masalah: array<int, array{tingkat: string, pesan: string}>, rekap: array}
     */
    public function pratinjau(Tournament $tournament, string $teksGrup, string $teksJadwal): array
    {
        $grup = $this->parseGrup($teksGrup);
        $jadwal = $this->parseJadwal($teksJadwal);

        $masalah = [];
        foreach ($grup['catatan'] as $c) {
            $masalah[] = ['tingkat' => 'peringatan', 'pesan' => $c];
        }
        foreach ($jadwal['catatan'] as $c) {
            $masalah[] = ['tingkat' => 'peringatan', 'pesan' => $c];
        }

        $timDb = $tournament->teams()->orderBy('nama')->get(['id', 'nama']);
        $namaDb = $timDb->pluck('nama')->all();

        $grupTim = [];
        $semuaNama = $namaDb;
        foreach ($grup['grup'] as $g) {
            foreach ($g['tim'] as $nama) {
                $grupTim[$this->kunci($nama)] = $g['kode'];
                $semuaNama[] = $nama;
            }
        }

        $masalah = array_merge($masalah, $this->periksaDuplikatGrup($grup['grup'], $timDb));

        $jadwal['baris'] = $this->cocokkanJadwal($jadwal['baris'], $semuaNama, $grupTim, $masalah);

        $masalah = array_merge($masalah, $this->periksaJadwal($jadwal['baris']));

        return [
            'grup' => $grup['grup'],
            'jadwal' => $jadwal['baris'],
            'masalah' => $masalah,
            'rekap' => $this->rekap($grup, $jadwal['baris']),
        ];
    }

    /**
     * @param  array<int, array{nama: string, kode: string, tim: array<int, string>}>  $grup
     * @param  Collection<int, Team>  $timDb
     * @return array<int, array{tingkat: string, pesan: string}>
     */
    protected function periksaDuplikatGrup(array $grup, $timDb): array
    {
        $masalah = [];
        $semuaDaftar = [];
        foreach ($grup as $g) {
            foreach ($g['tim'] as $nama) {
                $semuaDaftar[] = ['nama' => $nama, 'grup' => $g['nama']];
            }
        }

        $terlihat = [];
        foreach ($semuaDaftar as $item) {
            $kunci = $this->kunci($item['nama']);
            if (isset($terlihat[$kunci]) && $terlihat[$kunci] !== $item['nama']) {
                $masalah[] = ['tingkat' => 'kesalahan', 'pesan' => 'Nama hampir sama di dua tempat: "'.$terlihat[$kunci].'" dan "'.$item['nama'].'". Periksa manual.'];
            }
            $terlihat[$kunci] = $item['nama'];
        }

        foreach ($semuaDaftar as $item) {
            foreach ($timDb as $db) {
                if ($db->nama === $item['nama']) {
                    continue 2;
                }
                if ($this->mirip($db->nama, $item['nama']) >= 0.8) {
                    $masalah[] = ['tingkat' => 'peringatan', 'pesan' => 'Tim "'.$item['nama'].'" ('.$item['grup'].') mirip dengan tim yang sudah ada: "'.$db->nama.'". Tidak digabung otomatis.'];
                }
            }
        }

        return $masalah;
    }

    /**
     * @param  array<int, array{tanggal: ?string, jam: ?string, kode: ?string, tim_a: string, tim_b: string, grup: ?string, skor: ?string}>  $baris
     * @param  array<int, string>  $semuaNama
     * @param  array<string, string>  $grupTim
     * @param  array<int, array{tingkat: string, pesan: string}>  $masalah
     * @return array<int, array>
     */
    protected function cocokkanJadwal(array $baris, array $semuaNama, array $grupTim, array &$masalah): array
    {
        $hasil = [];

        foreach ($baris as $i => $row) {
            $a = $this->cocokkanNama($row['tim_a'], $semuaNama);
            $b = $this->cocokkanNama($row['tim_b'], $semuaNama);
            $barisMasalah = [];

            foreach (['a' => 'Tim A', 'b' => 'Tim B'] as $pos => $label) {
                $cocok = $pos === 'a' ? $a : $b;
                $asli = $row['tim_'.$pos];
                if ($cocok['tipe'] === 'tidak_ada') {
                    $masalah[] = ['tingkat' => 'kesalahan', 'pesan' => 'Baris '.($i + 1).': '.$label.' "'.$asli.'" tidak ditemukan pada daftar tim.'];
                    $barisMasalah[] = $label.' tidak cocok';
                } elseif ($cocok['tipe'] === 'mirip') {
                    $masalah[] = ['tingkat' => 'peringatan', 'pesan' => 'Baris '.($i + 1).': "'.$asli.'" mungkin dimaksud "'.$cocok['nama'].'" (ejaan berbeda).'];
                    $barisMasalah[] = 'Ejaan '.$label;
                }
            }

            $namaA = $a['nama'] ?? $row['tim_a'];
            $namaB = $b['nama'] ?? $row['tim_b'];

            $grupA = $grupTim[$this->kunci($namaA)] ?? null;
            $grupB = $grupTim[$this->kunci($namaB)] ?? null;

            if ($grupA && $grupB && $grupA !== $grupB) {
                $masalah[] = ['tingkat' => 'kesalahan', 'pesan' => 'Baris '.($i + 1).': '.$namaA.' (Grup '.$grupA.') vs '.$namaB.' (Grup '.$grupB.') tidak satu grup.'];
                $barisMasalah[] = 'Tidak sesuai grup';
            }

            $grupBaris = $row['grup'] ? strtoupper(trim(preg_replace('/^grup\s+/i', '', $row['grup']))) : null;
            if ($grupBaris && $grupA && $grupBaris !== $grupA) {
                $masalah[] = ['tingkat' => 'kesalahan', 'pesan' => 'Baris '.($i + 1).': grup tercatat "'.$grupBaris.'" berbeda dengan grup tim "'.$grupA.'".'];
                $barisMasalah[] = 'Grup tidak sesuai';
            }

            $tanggal = $this->normalisasiTanggal($row['tanggal']);
            if ($row['tanggal'] && ! $tanggal) {
                $masalah[] = ['tingkat' => 'peringatan', 'pesan' => 'Baris '.($i + 1).': tanggal "'.$row['tanggal'].'" tidak dikenali.'];
                $barisMasalah[] = 'Tanggal';
            }

            $jam = $this->normalisasiJam($row['jam']);
            if ($row['jam'] && ! $jam) {
                $masalah[] = ['tingkat' => 'peringatan', 'pesan' => 'Baris '.($i + 1).': jam "'.$row['jam'].'" tidak dikenali.'];
                $barisMasalah[] = 'Jam';
            }

            $hasil[] = [
                'tanggal' => $tanggal,
                'tanggal_teks' => $row['tanggal'],
                'jam' => $jam,
                'jam_teks' => $row['jam'],
                'kode' => $row['kode'] ? strtoupper($row['kode']) : null,
                'tim_a' => $namaA,
                'tim_b' => $namaB,
                'grup' => $grupA ?? $row['grup'],
                'skor' => $row['skor'],
                'perlu_konfirmasi' => $barisMasalah,
            ];
        }

        return $hasil;
    }

    /**
     * @param  array<int, array>  $baris
     * @return array<int, array{tingkat: string, pesan: string}>
     */
    protected function periksaJadwal(array $baris): array
    {
        $masalah = [];
        $kodeTerlihat = [];
        $pasanganTerlihat = [];
        $slot = [];

        foreach ($baris as $i => $row) {
            if (! empty($row['kode'])) {
                if (isset($kodeTerlihat[$row['kode']])) {
                    $masalah[] = ['tingkat' => 'kesalahan', 'pesan' => 'Kode pertandingan ganda: "'.$row['kode'].'" (baris '.($kodeTerlihat[$row['kode']] + 1).' dan '.($i + 1).').'];
                }
                $kodeTerlihat[$row['kode']] = $i;
            }

            $pasangan = $this->kunciPasangan($row['tim_a'], $row['tim_b']);
            if (isset($pasanganTerlihat[$pasangan])) {
                $masalah[] = ['tingkat' => 'peringatan', 'pesan' => 'Pasangan pertandingan ganda: '.$row['tim_a'].' vs '.$row['tim_b'].' muncul lebih dari sekali.'];
            }
            $pasanganTerlihat[$pasangan] = $i;

            if ($row['tanggal'] && $row['jam']) {
                foreach ([$row['tim_a'], $row['tim_b']] as $tim) {
                    $slotKunci = $row['tanggal'].' '.$row['jam'].'#'.$this->kunci($tim);
                    if (isset($slot[$slotKunci])) {
                        $masalah[] = ['tingkat' => 'kesalahan', 'pesan' => 'Tim '.$tim.' bertabrakan: bermain dua kali pada '.$row['tanggal'].' '.$row['jam'].'.'];
                    }
                    $slot[$slotKunci] = $i;
                }
            }
        }

        return $masalah;
    }

    /**
     * @param  array{grup: array<int, array{nama: string, kode: string, tim: array<int, string>>}}  $grup
     * @param  array<int, array>  $barisJadwal
     * @return array{jumlah_grup: int, jumlah_tim_unik: int, jumlah_pertandingan: int, rentang_tanggal: array{0: ?string, 1: ?string}, perlu_konfirmasi: array<int, array{baris: int, pesan: array<int, string>}>}
     */
    public function rekap(array $grup, array $barisJadwal): array
    {
        $timUnik = [];
        foreach ($grup['grup'] as $g) {
            foreach ($g['tim'] as $nama) {
                $timUnik[$this->kunci($nama)] = true;
            }
        }

        $tanggal = array_values(array_filter(array_map(fn ($r) => $r['tanggal'] ?? null, $barisJadwal)));
        sort($tanggal);

        $perlu = [];
        foreach ($barisJadwal as $i => $row) {
            if (! empty($row['perlu_konfirmasi'])) {
                $perlu[] = ['baris' => $i + 1, 'pesan' => $row['perlu_konfirmasi']];
            }
        }

        return [
            'jumlah_grup' => count($grup['grup']),
            'jumlah_tim_unik' => count($timUnik),
            'jumlah_pertandingan' => count($barisJadwal),
            'rentang_tanggal' => [$tanggal[0] ?? null, $tanggal[count($tanggal) - 1] ?? null],
            'perlu_konfirmasi' => $perlu,
        ];
    }

    /**
     * @param  array<int, string>  $semuaNama
     * @return array{tipe: string, nama: ?string, id: ?int}
     */
    protected function cocokkanNama(string $nama, array $semuaNama): array
    {
        $kunci = $this->kunci($nama);
        $terbaik = null;
        $rasioTerbaik = 0.0;

        foreach ($semuaNama as $kandidat) {
            if ($this->kunci($kandidat) === $kunci) {
                return ['tipe' => 'persis', 'nama' => $kandidat, 'id' => null];
            }
            $rasio = $this->mirip($kandidat, $nama);
            if ($rasio > $rasioTerbaik) {
                $rasioTerbaik = $rasio;
                $terbaik = $kandidat;
            }
        }

        if ($terbaik !== null && $rasioTerbaik >= 0.8) {
            return ['tipe' => 'mirip', 'nama' => $terbaik, 'id' => null];
        }

        return ['tipe' => 'tidak_ada', 'nama' => null, 'id' => null];
    }

    protected function kunci(string $nama): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower($nama)) ?? '';
    }

    protected function kunciPasangan(string $a, string $b): string
    {
        $k = [$this->kunci($a), $this->kunci($b)];
        sort($k);

        return implode('|', $k);
    }

    protected function mirip(string $a, string $b): float
    {
        $a = $this->kunci($a);
        $b = $this->kunci($b);
        if ($a === '' || $b === '') {
            return 0.0;
        }
        if ($a === $b) {
            return 1.0;
        }
        $max = max(strlen($a), strlen($b));

        return 1 - (levenshtein($a, $b) / $max);
    }

    protected function normalisasiTanggal(?string $teks): ?string
    {
        if (! $teks) {
            return null;
        }
        $teks = trim($teks);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $teks)) {
            return $teks;
        }
        if (preg_match('/^(\d{1,2})[\/\-.](\d{1,2})[\/\-.](\d{4})$/', $teks, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }
        if (preg_match('/(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})/u', $teks, $m)) {
            $bulan = self::BULAN[strtolower($m[2])] ?? null;
            if ($bulan) {
                return sprintf('%04d-%02d-%02d', $m[3], $bulan, $m[1]);
            }
        }

        return null;
    }

    protected function normalisasiJam(?string $teks): ?string
    {
        if (! $teks) {
            return null;
        }
        $teks = str_replace(['.', '•', ' '], [':', ':', ''], trim($teks));
        if (preg_match('/^(\d{1,2}):(\d{2})$/', $teks, $m)) {
            return sprintf('%02d:%02d', $m[1], $m[2]);
        }

        return null;
    }

    /**
     * Menyimpan data resmi ke database. Aman dipanggil berulang (idempoten).
     *
     * @return array{dibuat: int, diperbarui: int, dilewati: int, tim_dibuat: int, grup_dibuat: int, publikasi: bool}
     */
    public function simpan(Tournament $tournament, string $teksGrup, string $teksJadwal, bool $publikasikan): array
    {
        $data = $this->pratinjau($tournament, $teksGrup, $teksJadwal);

        $kesalahan = array_values(array_filter($data['masalah'], fn ($m) => $m['tingkat'] === 'kesalahan'));
        if ($kesalahan !== []) {
            throw ValidationException::withMessages([
                'impor' => 'Terdapat '.count($kesalahan).' masalah yang harus diperbaiki sebelum disimpan.',
            ]);
        }

        return DB::transaction(function () use ($tournament, $data, $publikasikan) {
            $round = $this->roundPertama($tournament);

            $grupDibuat = 0;
            $timDibuat = 0;
            $grupMap = [];

            foreach ($data['grup'] as $g) {
                $group = $round->groups()->where('nama', $g['nama'])->first();
                if (! $group) {
                    $group = $round->groups()->create([
                        'tournament_id' => $tournament->id,
                        'nama' => $g['nama'],
                        'urutan' => count($grupMap) + 1,
                    ]);
                    $grupDibuat++;
                }
                $grupMap[$g['kode']] = $group;

                $timIds = [];
                foreach ($g['tim'] as $nama) {
                    $team = $tournament->teams()->where('nama', $nama)->first();
                    if (! $team) {
                        $team = $tournament->teams()->create([
                            'nama' => $nama,
                            'status_pendaftaran' => 'terdaftar',
                        ]);
                        $timDibuat++;
                    }
                    $timIds[] = $team->id;
                }
                $group->teams()->sync($timIds);
            }

            $dibuat = 0;
            $diperbarui = 0;
            $dilewati = 0;
            $nomor = (int) $tournament->matches()->max('nomor_pertandingan');

            foreach ($data['jadwal'] as $row) {
                $timA = $tournament->teams()->where('nama', $row['tim_a'])->value('id');
                $timB = $tournament->teams()->where('nama', $row['tim_b'])->value('id');
                if (! $timA || ! $timB) {
                    $dilewati++;

                    continue;
                }

                $group = $row['grup'] ? ($grupMap[$row['grup']] ?? null) : null;

                $query = $tournament->matches()->where('round_id', $round->id);
                if (! empty($row['kode'])) {
                    $query->where('kode_pertandingan', $row['kode']);
                } else {
                    $query->where('tim_a_id', $timA)->where('tim_b_id', $timB);
                }
                $match = $query->first();

                $payload = [
                    'group_id' => $group?->id,
                    'kode_pertandingan' => $row['kode'],
                    'tanggal' => $row['tanggal'],
                    'jam' => $row['jam'],
                    'fase' => PhaseType::GRUP->value,
                ];

                if ($match) {
                    $terkunci = $match->status === MatchStatus::SELESAI
                        || $match->events()->exists()
                        || (int) $match->skor_a > 0 || (int) $match->skor_b > 0;

                    if ($terkunci) {
                        $dilewati++;

                        continue;
                    }

                    $match->update($payload);
                    $diperbarui++;

                    continue;
                }

                $tournament->matches()->create(array_merge($payload, [
                    'round_id' => $round->id,
                    'nomor_pertandingan' => ++$nomor,
                    'tim_a_id' => $timA,
                    'tim_b_id' => $timB,
                    'status' => MatchStatus::DIJADWALKAN->value,
                ]));
                $dibuat++;
            }

            $tournament->forceFill(['tampil_publik' => $publikasikan])->save();

            $this->audit->log(
                $tournament,
                $publikasikan ? 'impor_sahkan' : 'impor_draf',
                'round',
                $round->id,
                'Impor resmi: '.$dibuat.' dibuat, '.$diperbarui.' diperbarui, '.$dilewati.' dilewati'
            );

            return [
                'dibuat' => $dibuat,
                'diperbarui' => $diperbarui,
                'dilewati' => $dilewati,
                'tim_dibuat' => $timDibuat,
                'grup_dibuat' => $grupDibuat,
                'publikasi' => $publikasikan,
            ];
        });
    }

    protected function roundPertama(Tournament $tournament): Round
    {
        $round = $tournament->rounds()->where('nomor', 1)->first()
            ?? $tournament->rounds()->where('tipe', PhaseType::GRUP->value)->orderBy('nomor')->first();

        if ($round) {
            return $round;
        }

        return $tournament->rounds()->create([
            'nomor' => 1,
            'nama' => 'Putaran 1',
            'tipe' => PhaseType::GRUP->value,
        ]);
    }
}
