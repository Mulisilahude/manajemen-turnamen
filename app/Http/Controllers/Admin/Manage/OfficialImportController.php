<?php

namespace App\Http\Controllers\Admin\Manage;

use App\Http\Controllers\Admin\Concerns\ResolvesTournament;
use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Services\OfficialImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OfficialImportController extends Controller
{
    use ResolvesTournament;

    public function index(Tournament $tournament): View
    {
        $teksGrup = session('impor_teks_grup', $this->contoh('grup.txt'));
        $teksJadwal = session('impor_teks_jadwal', $this->contoh('jadwal.txt'));

        return view('admin.manage.impor.index', [
            'tournament' => $tournament,
            'teksGrup' => $teksGrup,
            'teksJadwal' => $teksJadwal,
            'pratinjau' => session('impor_pratinjau'),
            'hasil' => session('impor_hasil'),
            'tersimpan' => $this->rekapTersimpan($tournament),
        ]);
    }

    public function pratinjau(Request $request, Tournament $tournament, OfficialImportService $impor): RedirectResponse
    {
        $data = $this->validasi($request);

        $hasil = $impor->pratinjau($tournament, $data['teks_grup'], $data['teks_jadwal']);

        session([
            'impor_teks_grup' => $data['teks_grup'],
            'impor_teks_jadwal' => $data['teks_jadwal'],
            'impor_pratinjau' => $hasil,
        ]);

        $jumlahKesalahan = collect($hasil['masalah'])->where('tingkat', 'kesalahan')->count();

        return redirect()
            ->route('admin.manage.impor.index', $tournament)
            ->with($jumlahKesalahan ? 'error' : 'success', $jumlahKesalahan
                ? "Pratinjau dibuat, tetapi ada {$jumlahKesalahan} masalah yang perlu diperbaiki sebelum disimpan."
                : 'Pratinjau berhasil dibuat. Periksa daftar sebelum menyimpan atau menampilkan ke publik.');
    }

    public function simpan(Request $request, Tournament $tournament, OfficialImportService $impor): RedirectResponse
    {
        $data = $this->validasi($request, true);

        try {
            $hasil = $impor->simpan($tournament, $data['teks_grup'], $data['teks_jadwal'], $data['mode'] === 'sahkan');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        session()->forget(['impor_pratinjau']);

        $pesan = $data['mode'] === 'sahkan'
            ? "Data resmi disimpan dan ditampilkan ke publik. {$hasil['dibuat']} pertandingan baru, {$hasil['diperbarui']} diperbarui."
            : "Data resmi disimpan sebagai DRAF (belum tampil ke publik). {$hasil['dibuat']} pertandingan baru, {$hasil['diperbarui']} diperbarui.";

        return redirect()
            ->route('admin.manage.impor.index', $tournament)
            ->with('success', $pesan)
            ->with('impor_hasil', $hasil);
    }

    public function reset(Tournament $tournament): RedirectResponse
    {
        session()->forget(['impor_pratinjau', 'impor_teks_grup', 'impor_teks_jadwal', 'impor_hasil']);

        return redirect()
            ->route('admin.manage.impor.index', $tournament)
            ->with('success', 'Pratinjau impor dibersihkan.');
    }

    /**
     * @return array{teks_grup: string, teks_jadwal: string, mode?: string}
     */
    protected function validasi(Request $request, bool $denganMode = false): array
    {
        return $request->validate([
            'teks_grup' => 'nullable|string',
            'teks_jadwal' => 'nullable|string',
            'mode' => $denganMode ? 'required|in:draf,sahkan' : 'nullable',
        ]) + ['mode' => $request->input('mode', 'draf')];
    }

    /**
     * @return array{tim: int, grup: int, pertandingan: int, rentang: array{0: ?string, 1: ?string}, publik: bool, belum_pemain: array<int, string>}
     */
    protected function rekapTersimpan(Tournament $tournament): array
    {
        $tanggal = $tournament->matches()
            ->whereNotNull('tanggal')
            ->orderBy('tanggal')
            ->pluck('tanggal')
            ->map(fn ($t) => $t?->format('Y-m-d'));

        $belumPemain = $tournament->teams()
            ->whereDoesntHave('players')
            ->orderBy('nama')
            ->pluck('nama')
            ->all();

        return [
            'tim' => $tournament->teams()->count(),
            'grup' => $tournament->rounds()->where('nomor', 1)->first()?->groups()->count() ?? 0,
            'pertandingan' => $tournament->matches()->count(),
            'rentang' => [$tanggal->first(), $tanggal->last()],
            'publik' => (bool) $tournament->tampil_publik,
            'belum_pemain' => $belumPemain,
        ];
    }

    protected function contoh(string $berkas): string
    {
        $path = storage_path('app/import/'.$berkas);

        return is_file($path) ? (string) file_get_contents($path) : '';
    }
}
