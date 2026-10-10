<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\TournamentStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class TournamentController extends Controller
{
    public function index(): View
    {
        $tournaments = Tournament::latest()->paginate(12);

        return view('admin.tournaments.index', compact('tournaments'));
    }

    public function create(): View
    {
        return view('admin.tournaments.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'cabang_olahraga' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'lokasi' => 'nullable|string|max:255',
            'status' => ['required', new Enum(TournamentStatus::class)],
            'pengaturan_format' => 'nullable|array',
        ]);

        $validated['slug'] = $this->generateSlug($validated['nama']);

        $tournament = Tournament::create($validated);

        return redirect()->route('admin.tournaments.index')->with('success', 'Turnamen berhasil dibuat.');
    }

    public function edit(Tournament $tournament): View
    {
        return view('admin.tournaments.edit', compact('tournament'));
    }

    public function update(Request $request, Tournament $tournament): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'cabang_olahraga' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'lokasi' => 'nullable|string|max:255',
            'status' => ['required', new Enum(TournamentStatus::class)],
            'pengaturan_format' => 'nullable|array',
        ]);

        if ($tournament->nama !== $validated['nama']) {
            $validated['slug'] = $this->generateSlug($validated['nama'], $tournament->id);
        }

        $tournament->update($validated);

        return redirect()->route('admin.tournaments.index')->with('success', 'Turnamen berhasil diperbarui.');
    }

    public function setActive(Tournament $tournament): RedirectResponse
    {
        // Mark as active (simple placeholder)
        $tournament->status = TournamentStatus::ACTIVE;
        $tournament->save();

        return redirect()->route('admin.manage.dashboard', $tournament)->with('success', 'Turnamen dipilih untuk dikelola.');
    }

    protected function generateSlug(string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name);
        $count = 1;
        $query = Tournament::where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        while ($query->exists()) {
            $slug = Str::slug($name).'-'.$count++;
            $query = Tournament::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }
}
