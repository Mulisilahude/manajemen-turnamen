<?php

namespace App\Http\Controllers\Admin\Manage;

use App\Http\Controllers\Admin\Concerns\ResolvesTournament;
use App\Http\Controllers\Controller;
use App\Models\Round;
use App\Models\Tournament;
use App\Services\PenjadwalanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JadwalController extends Controller
{
    use ResolvesTournament;

    public function index(Tournament $tournament, Round $round, PenjadwalanService $penjadwalan): View
    {
        $this->ensureOwned($round, $tournament);

        $round->load(['groups.teams', 'matches.teamA', 'matches.teamB']);
        $draft = session("jadwal_round_{$round->id}");
        $sudahAdaJadwal = $round->matches()->whereIn('status', ['berlangsung', 'selesai'])->exists();

        return view('admin.manage.rounds.jadwal', [
            'tournament' => $tournament,
            'round' => $round,
            'draft' => $draft,
            'sudahAdaJadwal' => $sudahAdaJadwal,
            'namaTim' => $this->petaTim($tournament),
            'namaWasit' => $tournament->referees()->orderBy('nama')->pluck('nama', 'id'),
            'config' => $this->configAwal($tournament, $draft['config'] ?? []),
        ]);
    }

    public function proses(Request $request, Tournament $tournament, Round $round, PenjadwalanService $penjadwalan): RedirectResponse
    {
        $this->ensureOwned($round, $tournament);

        $validated = $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i',
            'durasi' => 'required|integer|min:5|max:240',
            'jeda' => 'nullable|integer|min:0|max:120',
            'lapangan' => 'nullable|string',
            'hari' => 'nullable|array',
            'hari.*' => 'integer|between:1,7',
            'dua_putaran' => 'nullable|boolean',
        ]);

        $config = [
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'] ?? null,
            'jam_mulai' => $validated['jam_mulai'],
            'jam_selesai' => $validated['jam_selesai'],
            'durasi' => (int) $validated['durasi'],
            'jeda' => (int) ($validated['jeda'] ?? 0),
            'lapangan' => $this->pecahLapangan($validated['lapangan'] ?? ''),
            'hari' => array_map('intval', (array) ($validated['hari'] ?? [])),
            'dua_putaran' => (bool) ($validated['dua_putaran'] ?? false),
        ];

        $hasil = $penjadwalan->generateJadwal($round, $config);

        session(["jadwal_round_{$round->id}" => [
            'config' => $config,
            'rows' => $hasil['rows'],
            'errors' => $hasil['errors'],
        ]]);

        $jumlah = count($hasil['rows']);

        return redirect()
            ->route('admin.manage.jadwal.index', [$tournament, $round])
            ->with($hasil['errors'] ? 'error' : 'success', $hasil['errors']
                ? 'Pratinjau dibuat, namun ada bentrok/kekurangan slot yang perlu diperbaiki.'
                : "Pratinjau {$jumlah} pertandingan berhasil dibuat. Periksa sebelum disahkan.");
    }

    public function sahkan(Tournament $tournament, Round $round, PenjadwalanService $penjadwalan): RedirectResponse
    {
        $this->ensureOwned($round, $tournament);

        $draft = session("jadwal_round_{$round->id}");

        if (! $draft || empty($draft['rows'])) {
            return back()->with('error', 'Tidak ada pratinjau jadwal untuk disahkan.');
        }

        if (! empty($draft['errors'])) {
            return back()->with('error', 'Jadwal belum dapat disahkan karena masih ada bentrok. Perbaiki konfigurasi terlebih dahulu.');
        }

        $jumlah = $penjadwalan->simpanJadwal($round, $draft['rows']);

        session()->forget("jadwal_round_{$round->id}");

        return redirect()
            ->route('admin.manage.matches.index', $tournament)
            ->with('success', "{$jumlah} jadwal pertandingan berhasil disimpan.");
    }

    public function batal(Tournament $tournament, Round $round): RedirectResponse
    {
        $this->ensureOwned($round, $tournament);

        session()->forget("jadwal_round_{$round->id}");

        return redirect()
            ->route('admin.manage.jadwal.index', [$tournament, $round])
            ->with('success', 'Pratinjau jadwal dibatalkan.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function configAwal(Tournament $tournament, array $override): array
    {
        $default = [
            'tanggal_mulai' => optional($tournament->tanggal_mulai)->format('Y-m-d') ?? now()->toDateString(),
            'tanggal_selesai' => optional($tournament->tanggal_selesai)->format('Y-m-d') ?? now()->addDays(14)->toDateString(),
            'jam_mulai' => '08:00',
            'jam_selesai' => '17:00',
            'durasi' => 60,
            'jeda' => 15,
            'lapangan' => array_values(array_filter([$tournament->lokasi])),
            'hari' => [],
            'dua_putaran' => false,
        ];

        return array_merge($default, $override);
    }

    /**
     * @return array<int, string>
     */
    protected function pecahLapangan(string $teks): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $teks)), fn ($v) => $v !== ''));
    }

    /**
     * @return array<int, string>
     */
    protected function petaTim(Tournament $tournament): array
    {
        return $tournament->teams()->orderBy('nama')->pluck('nama', 'id')->all();
    }
}
