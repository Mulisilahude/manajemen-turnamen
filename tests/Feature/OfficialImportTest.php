<?php

namespace Tests\Feature;

use App\Models\MatchGame;
use App\Models\Team;
use App\Models\Tournament;
use App\Models\User;
use App\Services\OfficialImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OfficialImportTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    protected function grupTeks(): string
    {
        return "Grup A\nAlfa FC\nBravo FC\nCharlie FC\nDelta FC\nGrup B\nEcho FC\nFoxtrot FC\nGolf FC\nHotel FC\n";
    }

    protected function jadwalTeks(): string
    {
        return implode("\n", [
            'Tanggal | Jam | Kode | Tim A | Tim B | Grup | Skor',
            '2026-10-10 | 20:15 | A1 | Alfa FC | Bravo FC | A |',
            '2026-10-10 | 21:30 | A2 | Charlie FC | Delta FC | A |',
            '2026-10-11 | 20:15 | B1 | Echo FC | Foxtrot FC | B |',
            '2026-10-11 | 21:30 | B2 | Golf FC | Hotel FC | B |',
        ])."\n";
    }

    public function test_pratinjau_parses_groups_schedule_and_reports_no_errors(): void
    {
        $tournament = Tournament::factory()->create();

        $hasil = app(OfficialImportService::class)->pratinjau($tournament, $this->grupTeks(), $this->jadwalTeks());

        $this->assertSame(2, $hasil['rekap']['jumlah_grup']);
        $this->assertSame(8, $hasil['rekap']['jumlah_tim_unik']);
        $this->assertSame(4, $hasil['rekap']['jumlah_pertandingan']);
        $this->assertSame([], collect($hasil['masalah'])->where('tingkat', 'kesalahan')->all());
        $this->assertSame('2026-10-10', $hasil['rekap']['rentang_tanggal'][0]);
        $this->assertSame('2026-10-11', $hasil['rekap']['rentang_tanggal'][1]);
    }

    public function test_pratinjau_flags_unknown_team_as_error(): void
    {
        $tournament = Tournament::factory()->create();

        $jadwal = $this->jadwalTeks()."2026-10-12 | 20:15 | A3 | Alfa FC | Zulu FC | A |\n";

        $hasil = app(OfficialImportService::class)->pratinjau($tournament, $this->grupTeks(), $jadwal);

        $this->assertNotEmpty(collect($hasil['masalah'])->where('tingkat', 'kesalahan')->all());
    }

    public function test_simpan_draft_persists_and_hides_from_public(): void
    {
        $tournament = Tournament::factory()->create(['tampil_publik' => true]);

        $hasil = app(OfficialImportService::class)
            ->simpan($tournament, $this->grupTeks(), $this->jadwalTeks(), false);

        $this->assertSame(4, $hasil['dibuat']);
        $this->assertSame(8, $hasil['tim_dibuat']);
        $this->assertSame(2, $hasil['grup_dibuat']);
        $this->assertFalse($tournament->fresh()->tampil_publik);

        $this->assertSame(8, $tournament->teams()->count());
        $this->assertSame(4, $tournament->matches()->count());
        $this->assertSame(2, $tournament->rounds()->where('nomor', 1)->first()->groups()->count());

        $this->get("/turnamen/{$tournament->slug}")->assertStatus(404);
    }

    public function test_simpan_sahkan_publishes_tournament(): void
    {
        $tournament = Tournament::factory()->create(['tampil_publik' => false]);

        app(OfficialImportService::class)
            ->simpan($tournament, $this->grupTeks(), $this->jadwalTeks(), true);

        $this->assertTrue($tournament->fresh()->tampil_publik);

        $this->get("/turnamen/{$tournament->slug}")->assertStatus(200)->assertSee('Alfa FC');
    }

    public function test_simpan_is_idempotent(): void
    {
        $tournament = Tournament::factory()->create();
        $service = app(OfficialImportService::class);

        $service->simpan($tournament, $this->grupTeks(), $this->jadwalTeks(), false);
        $kedua = $service->simpan($tournament, $this->grupTeks(), $this->jadwalTeks(), false);

        $this->assertSame(0, $kedua['dibuat']);
        $this->assertSame(4, $kedua['diperbarui']);
        $this->assertSame(4, $tournament->matches()->count());
        $this->assertSame(1, $tournament->rounds()->count());
    }

    public function test_simpan_blocks_when_schedule_has_unknown_team(): void
    {
        $tournament = Tournament::factory()->create();

        $this->expectException(ValidationException::class);

        app(OfficialImportService::class)
            ->simpan($tournament, $this->grupTeks(), $this->jadwalTeks()."2026-10-12 | 20:15 | | Alfa FC | Zulu FC | A |\n", false);
    }

    public function test_import_does_not_overwrite_locked_match(): void
    {
        $tournament = Tournament::factory()->create();
        $round = $tournament->rounds()->create([
            'nomor' => 1, 'nama' => 'Putaran 1', 'tipe' => 'grup',
        ]);
        $alfa = Team::create(['tournament_id' => $tournament->id, 'nama' => 'Alfa FC', 'status_pendaftaran' => 'terdaftar']);
        $bravo = Team::create(['tournament_id' => $tournament->id, 'nama' => 'Bravo FC', 'status_pendaftaran' => 'terdaftar']);

        $match = MatchGame::create([
            'tournament_id' => $tournament->id,
            'round_id' => $round->id,
            'tim_a_id' => $alfa->id,
            'tim_b_id' => $bravo->id,
            'status' => 'selesai',
            'skor_a' => 3,
            'skor_b' => 1,
        ]);

        app(OfficialImportService::class)
            ->simpan($tournament, $this->grupTeks(), $this->jadwalTeks(), false);

        $match->refresh();
        $this->assertSame(3, $match->skor_a);
        $this->assertSame(1, $match->skor_b);
        $this->assertSame('selesai', $match->status->value);
    }

    public function test_admin_import_page_renders(): void
    {
        $tournament = Tournament::factory()->create();

        $this->actingAs($this->admin())
            ->get("/admin/turnamen/{$tournament->slug}/impor")
            ->assertStatus(200)
            ->assertSee('Impor Data Resmi');
    }

    public function test_admin_can_preview_and_save_via_http(): void
    {
        $tournament = Tournament::factory()->create();

        $this->actingAs($this->admin());

        $this->post("/admin/turnamen/{$tournament->slug}/impor/pratinjau", [
            'teks_grup' => $this->grupTeks(),
            'teks_jadwal' => $this->jadwalTeks(),
        ])->assertRedirect()->assertSessionHas('success');

        $this->post("/admin/turnamen/{$tournament->slug}/impor/simpan", [
            'teks_grup' => $this->grupTeks(),
            'teks_jadwal' => $this->jadwalTeks(),
            'mode' => 'sahkan',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertTrue($tournament->fresh()->tampil_publik);
        $this->assertSame(4, $tournament->matches()->count());
    }
}
