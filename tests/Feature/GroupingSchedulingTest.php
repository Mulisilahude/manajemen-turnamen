<?php

namespace Tests\Feature;

use App\Enums\PhaseType;
use App\Models\MatchGame;
use App\Models\Round;
use App\Models\Team;
use App\Models\Tournament;
use App\Models\User;
use App\Services\PenjadwalanService;
use App\Services\PutaranService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class GroupingSchedulingTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    /**
     * @return array{0: Tournament, 1: Round, 2: Collection<int, Team>}
     */
    protected function turnamenGrup(int $jumlahTim = 4, int $jumlahGrup = 1): array
    {
        $tournament = Tournament::factory()->create();
        $teams = collect(range(1, $jumlahTim))->map(fn ($i) => Team::create([
            'tournament_id' => $tournament->id,
            'nama' => "Tim {$i}",
            'status_pendaftaran' => 'terdaftar',
        ]));

        $round = app(PutaranService::class)->buatPutaran($tournament, 'Grup Awal', PhaseType::GRUP, $jumlahGrup);

        return [$tournament, $round, $teams];
    }

    public function test_manual_group_assignment_saves_placements(): void
    {
        [$tournament, $round, $teams] = $this->turnamenGrup(4, 2);
        $groups = $round->groups;
        $putaran = app(PutaranService::class);

        $putaran->simpanPenempatanGrup($round, [
            $groups[0]->id => [$teams[0]->id, $teams[1]->id],
            $groups[1]->id => [$teams[2]->id, $teams[3]->id],
        ]);

        $this->assertSame(2, $groups[0]->fresh()->teams()->count());
        $this->assertTrue($groups[1]->fresh()->teams->contains($teams[3]->id));
    }

    public function test_manual_group_assignment_rejects_team_in_two_groups(): void
    {
        [$tournament, $round, $teams] = $this->turnamenGrup(4, 2);
        $groups = $round->groups;

        $this->expectException(ValidationException::class);

        app(PutaranService::class)->simpanPenempatanGrup($round, [
            $groups[0]->id => [$teams[0]->id, $teams[1]->id],
            $groups[1]->id => [$teams[0]->id, $teams[2]->id],
        ]);
    }

    public function test_group_assignment_blocked_when_round_has_matches(): void
    {
        [$tournament, $round, $teams] = $this->turnamenGrup(4, 2);
        $groups = $round->groups;

        MatchGame::create([
            'tournament_id' => $tournament->id, 'round_id' => $round->id, 'group_id' => $groups[0]->id,
            'tim_a_id' => $teams[0]->id, 'tim_b_id' => $teams[1]->id, 'status' => 'dijadwalkan',
        ]);

        $this->expectException(ValidationException::class);

        app(PutaranService::class)->simpanPenempatanGrup($round, [
            $groups[0]->id => [$teams[0]->id],
        ]);
    }

    public function test_undian_distributes_all_teams_evenly_without_duplicates(): void
    {
        [$tournament, $round, $teams] = $this->turnamenGrup(6, 1);

        $groups = app(PutaranService::class)->generateUndian($round, 3);

        $this->assertCount(3, $groups);

        $semua = array_merge(...array_map(fn ($g) => $g['tim_ids'], $groups));
        $this->assertCount(6, $semua);
        $this->assertCount(6, array_unique($semua));
        $this->assertSame([2, 2, 2], array_map(fn ($g) => count($g['tim_ids']), $groups));
    }

    public function test_undian_commit_persists_groups_and_teams(): void
    {
        [$tournament, $round, $teams] = $this->turnamenGrup(4, 2);
        $putaran = app(PutaranService::class);

        $groups = $putaran->generateUndian($round, 2);
        $putaran->sahkanUndian($round, $groups);

        $this->assertSame(4, $round->groups()->withCount('teams')->get()->sum('teams_count'));
    }

    public function test_undian_requires_valid_group_count(): void
    {
        [$tournament, $round, $teams] = $this->turnamenGrup(2, 2);

        $this->expectException(ValidationException::class);
        app(PutaranService::class)->generateUndian($round, 5);
    }

    public function test_automatic_schedule_generates_round_robin_without_team_clash(): void
    {
        [$tournament, $round, $teams] = $this->turnamenGrup(4, 2);
        $putaran = app(PutaranService::class);
        $groups = $round->groups;
        $putaran->simpanPenempatanGrup($round, [
            $groups[0]->id => [$teams[0]->id, $teams[1]->id],
            $groups[1]->id => [$teams[2]->id, $teams[3]->id],
        ]);

        $hasil = app(PenjadwalanService::class)->generateJadwal($round, [
            'tanggal_mulai' => '2026-06-01',
            'tanggal_selesai' => '2026-06-01',
            'jam_mulai' => '08:00',
            'jam_selesai' => '12:00',
            'durasi' => 60,
            'jeda' => 0,
            'lapangan' => ['Lapangan A'],
            'hari' => [],
            'dua_putaran' => false,
        ]);

        $this->assertSame([], $hasil['errors']);
        $this->assertCount(2, $hasil['rows']);

        foreach ($rows = $hasil['rows'] as $row) {
            $this->assertNotNull($row['tanggal']);
            $this->assertNotNull($row['jam']);
        }

        $kunci = array_map(fn ($r) => $r['tanggal'].'|'.$r['jam'], $rows);
        $this->assertCount(count($kunci), array_unique($kunci));
    }

    public function test_automatic_scheduling_reports_insufficient_slots(): void
    {
        [$tournament, $round, $teams] = $this->turnamenGrup(4, 1);
        $putaran = app(PutaranService::class);
        $putaran->isiGrup($round->groups->first(), $teams->pluck('id')->all());

        $hasil = app(PenjadwalanService::class)->generateJadwal($round, [
            'tanggal_mulai' => '2026-10-10',
            'tanggal_selesai' => '2026-10-10',
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
            'durasi' => 60,
            'jeda' => 0,
            'lapangan' => ['Lapangan A'],
            'hari' => [],
            'dua_putaran' => false,
        ]);

        $this->assertNotEmpty($hasil['errors']);
    }

    public function test_collision_detection_flags_double_booked_team_and_venue(): void
    {
        [$tournament, $round, $teams] = $this->turnamenGrup(3, 1);
        $service = app(PenjadwalanService::class);

        $errors = $service->validasiBentrok($tournament->id, [
            ['tanggal' => '2026-10-10', 'jam' => '08:00', 'lokasi' => 'Lapangan A', 'tim_a_id' => $teams[0]->id, 'tim_b_id' => $teams[1]->id],
            ['tanggal' => '2026-10-10', 'jam' => '08:00', 'lokasi' => 'Lapangan A', 'tim_a_id' => $teams[1]->id, 'tim_b_id' => $teams[2]->id],
        ]);

        $this->assertNotEmpty($errors);
    }

    public function test_save_schedule_blocked_when_round_locked(): void
    {
        [$tournament, $round, $teams] = $this->turnamenGrup(2, 1);
        $putaran = app(PutaranService::class);
        $putaran->isiGrup($round->groups->first(), $teams->pluck('id')->all());

        MatchGame::create([
            'tournament_id' => $tournament->id, 'round_id' => $round->id,
            'tim_a_id' => $teams[0]->id, 'tim_b_id' => $teams[1]->id,
            'status' => 'selesai',
        ]);

        $this->expectException(ValidationException::class);

        app(PenjadwalanService::class)->simpanJadwal($round, [
            ['tim_a_id' => $teams[0]->id, 'tim_b_id' => $teams[1]->id, 'tanggal' => '2026-10-10', 'jam' => '08:00', 'lokasi' => 'Lapangan A'],
        ]);
    }

    public function test_save_schedule_persists_matches(): void
    {
        [$tournament, $round, $teams] = $this->turnamenGrup(4, 1);
        $putaran = app(PutaranService::class);
        $putaran->isiGrup($round->groups->first(), $teams->pluck('id')->all());

        $service = app(PenjadwalanService::class);
        $hasil = $service->generateJadwal($round, [
            'tanggal_mulai' => '2026-10-10',
            'tanggal_selesai' => '2026-10-12',
            'jam_mulai' => '08:00',
            'jam_selesai' => '16:00',
            'durasi' => 60,
            'jeda' => 30,
            'lapangan' => ['Lapangan A', 'Lapangan B'],
            'hari' => [],
            'dua_putaran' => false,
        ]);

        $jumlah = app(PenjadwalanService::class)->simpanJadwal($round, $hasil['rows']);

        $this->assertSame(6, $jumlah);
        $this->assertDatabaseCount('matches', 6);
    }

    public function test_public_schedule_filters_by_group_and_date(): void
    {
        [$tournament, $round, $teams] = $this->turnamenGrup(4, 2);
        $groups = $round->groups;
        app(PutaranService::class)->simpanPenempatanGrup($round, [
            $groups[0]->id => [$teams[0]->id, $teams[1]->id],
            $groups[1]->id => [$teams[2]->id, $teams[3]->id],
        ]);

        MatchGame::create([
            'tournament_id' => $tournament->id, 'round_id' => $round->id, 'group_id' => $groups[0]->id,
            'tim_a_id' => $teams[0]->id, 'tim_b_id' => $teams[1]->id,
            'tanggal' => '2026-10-10', 'jam' => '08:00', 'status' => 'dijadwalkan',
        ]);
        MatchGame::create([
            'tournament_id' => $tournament->id, 'round_id' => $round->id, 'group_id' => $groups[1]->id,
            'tim_a_id' => $teams[2]->id, 'tim_b_id' => $teams[3]->id,
            'tanggal' => '2026-10-11', 'jam' => '09:00', 'status' => 'dijadwalkan',
        ]);

        $this->get("/turnamen/{$tournament->slug}/jadwal?grup={$groups[0]->id}")
            ->assertStatus(200)
            ->assertSee($teams[0]->nama)
            ->assertDontSee($teams[2]->nama);

        $this->get("/turnamen/{$tournament->slug}/jadwal?tanggal=2026-10-11")
            ->assertStatus(200)
            ->assertSee($teams[2]->nama)
            ->assertDontSee($teams[0]->nama);
    }

    public function test_group_and_schedule_admin_pages_render(): void
    {
        [$tournament, $round, $teams] = $this->turnamenGrup(4, 2);

        $this->actingAs($this->admin());

        $this->get("/admin/turnamen/{$tournament->slug}/putaran/{$round->id}/grup")->assertStatus(200);
        $this->get("/admin/turnamen/{$tournament->slug}/putaran/{$round->id}/undian")->assertStatus(200);
        $this->get("/admin/turnamen/{$tournament->slug}/putaran/{$round->id}/jadwal")->assertStatus(200);
    }

    public function test_undian_draft_flow_via_http(): void
    {
        [$tournament, $round, $teams] = $this->turnamenGrup(4, 2);

        $this->actingAs($this->admin());

        $this->post("/admin/turnamen/{$tournament->slug}/putaran/{$round->id}/undian/proses", ['jumlah_grup' => 2])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->get("/admin/turnamen/{$tournament->slug}/putaran/{$round->id}/undian")->assertStatus(200);

        $this->post("/admin/turnamen/{$tournament->slug}/putaran/{$round->id}/undian/sahkan")
            ->assertRedirect();

        $this->assertSame(4, $round->groups()->withCount('teams')->get()->sum('teams_count'));
    }

    public function test_schedule_draft_preview_renders_after_generation(): void
    {
        [$tournament, $round, $teams] = $this->turnamenGrup(4, 2);
        $putaran = app(PutaranService::class);
        $groups = $round->groups;
        $putaran->simpanPenempatanGrup($round, [
            $groups[0]->id => [$teams[0]->id, $teams[1]->id],
            $groups[1]->id => [$teams[2]->id, $teams[3]->id],
        ]);

        $this->actingAs($this->admin());

        $this->post("/admin/turnamen/{$tournament->slug}/putaran/{$round->id}/jadwal/proses", [
            'tanggal_mulai' => '2026-10-10',
            'tanggal_selesai' => '2026-10-11',
            'jam_mulai' => '08:00',
            'jam_selesai' => '12:00',
            'durasi' => 60,
            'jeda' => 0,
            'lapangan' => "Lapangan A\nLapangan B",
        ])->assertRedirect()->assertSessionHas('success');

        $this->get("/admin/turnamen/{$tournament->slug}/putaran/{$round->id}/jadwal")
            ->assertStatus(200)
            ->assertSee($teams[0]->nama);
    }
}
