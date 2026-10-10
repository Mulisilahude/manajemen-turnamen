<?php

namespace Tests\Feature;

use App\Enums\PhaseType;
use App\Models\Group;
use App\Models\MatchGame;
use App\Models\Round;
use App\Models\Team;
use App\Models\Tournament;
use App\Models\User;
use App\Services\KlasemenService;
use App\Services\PertandinganService;
use App\Services\PutaranService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManageTournamentTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_progression_schedule_and_detail_pages_render_with_data(): void
    {
        $tournament = Tournament::factory()->create();
        $teams = collect(range(1, 4))->map(fn ($i) => Team::create([
            'tournament_id' => $tournament->id,
            'nama' => "Regu {$i}",
            'status_pendaftaran' => 'terdaftar',
        ]));

        $putaran = app(PutaranService::class);
        $scores = app(PertandinganService::class);

        $round = $putaran->buatPutaran($tournament, 'Grup Awal', PhaseType::GRUP, 1);
        $group = $round->groups->first();
        $putaran->isiGrup($group, $teams->pluck('id')->all());

        $this->actingAs($this->admin());

        foreach ([
            "/admin/turnamen/{$tournament->slug}/putaran/{$round->id}/lanjut",
            "/admin/turnamen/{$tournament->slug}/putaran/{$round->id}/grup",
            "/admin/turnamen/{$tournament->slug}/putaran/{$round->id}/undian",
            "/admin/turnamen/{$tournament->slug}/putaran/{$round->id}/jadwal",
        ] as $url) {
            $this->get($url)->assertOk();
        }

        $match = MatchGame::create([
            'tournament_id' => $tournament->id, 'round_id' => $round->id, 'group_id' => $group->id,
            'nomor_pertandingan' => 1,
            'tim_a_id' => $teams[0]->id, 'tim_b_id' => $teams[1]->id,
            'status' => 'selesai', 'skor_a' => 1, 'skor_b' => 0,
        ]);
        $scores->tentukanPemenang($match);

        $this->actingAs($this->admin());

        $this->get("/admin/turnamen/{$tournament->slug}/putaran/{$round->id}/lanjut")->assertStatus(200);
        $this->get("/admin/turnamen/{$tournament->slug}/pertandingan/{$match->id}/edit")->assertStatus(200);

        $this->get("/turnamen/{$tournament->slug}/klasemen")->assertStatus(200);
        $this->get("/turnamen/{$tournament->slug}/bagan")->assertStatus(200);
        $this->get("/turnamen/{$tournament->slug}/pertandingan/{$match->id}")->assertStatus(200);
    }

    public function test_guest_is_redirected_from_manage_area(): void
    {
        $tournament = Tournament::factory()->create();

        $this->get("/admin/turnamen/{$tournament->slug}")->assertRedirect('/login');
    }

    public function test_admin_can_create_team_player_and_referee(): void
    {
        $tournament = Tournament::factory()->create();

        $this->actingAs($this->admin());

        $this->post("/admin/turnamen/{$tournament->slug}/tim", [
            'nama' => 'Garuda FC',
            'singkatan' => 'GRD',
            'pelatih' => 'Budi',
            'status_pendaftaran' => 'terdaftar',
        ])->assertRedirect();

        $team = Team::firstWhere('nama', 'Garuda FC');
        $this->assertNotNull($team);
        $this->assertSame($tournament->id, $team->tournament_id);

        $this->post("/admin/turnamen/{$tournament->slug}/pemain", [
            'team_id' => $team->id,
            'nama' => 'Andi',
            'nomor_punggung' => 10,
            'posisi' => 'Penyerang',
        ])->assertRedirect();

        $this->assertDatabaseHas('players', ['nama' => 'Andi', 'nomor_punggung' => 10, 'tournament_id' => $tournament->id]);

        $this->post("/admin/turnamen/{$tournament->slug}/wasit", [
            'nama' => 'Wasit Satu',
            'nomor_kontak' => '08123',
        ])->assertRedirect();

        $this->assertDatabaseHas('referees', ['nama' => 'Wasit Satu', 'tournament_id' => $tournament->id]);
    }

    public function test_duplicate_shirt_number_in_same_team_is_rejected(): void
    {
        $tournament = Tournament::factory()->create();
        $team = $tournament->teams()->create(['nama' => 'Tim A', 'status_pendaftaran' => 'terdaftar']);
        $team->players()->create(['tournament_id' => $tournament->id, 'nama' => 'Andi', 'nomor_punggung' => 10]);

        $this->actingAs($this->admin())
            ->post("/admin/turnamen/{$tournament->slug}/pemain", [
                'team_id' => $team->id,
                'nama' => 'Rian',
                'nomor_punggung' => 10,
            ])
            ->assertSessionHasErrors('nomor_punggung');
    }

    public function test_team_from_other_tournament_cannot_be_updated(): void
    {
        $a = Tournament::factory()->create();
        $b = Tournament::factory()->create();
        $teamB = Team::create(['tournament_id' => $b->id, 'nama' => 'Tim B', 'status_pendaftaran' => 'terdaftar']);

        $this->actingAs($this->admin())
            ->put("/admin/turnamen/{$a->slug}/tim/{$teamB->id}", [
                'nama' => 'Berubah',
                'status_pendaftaran' => 'terdaftar',
            ])
            ->assertNotFound();
    }

    public function test_all_manage_pages_render(): void
    {
        $tournament = Tournament::factory()->create();

        $this->actingAs($this->admin());

        foreach ([
            "/admin/turnamen/{$tournament->slug}",
            "/admin/turnamen/{$tournament->slug}/tim",
            "/admin/turnamen/{$tournament->slug}/pemain",
            "/admin/turnamen/{$tournament->slug}/wasit",
            "/admin/turnamen/{$tournament->slug}/putaran",
            "/admin/turnamen/{$tournament->slug}/pertandingan",
            "/admin/turnamen/{$tournament->slug}/pertandingan/baru",
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_schedule_page_renders_with_rounds_and_status_filter(): void
    {
        $tournament = Tournament::factory()->create();
        $round = $tournament->rounds()->create(['nomor' => 1, 'nama' => 'Fase Grup', 'tipe' => PhaseType::GRUP]);

        $this->actingAs($this->admin());

        $this->get("/admin/turnamen/{$tournament->slug}/pertandingan")->assertOk();

        foreach (['dijadwalkan', 'berlangsung', 'selesai', 'ditunda', 'dibatalkan'] as $status) {
            $this->get("/admin/turnamen/{$tournament->slug}/pertandingan?status={$status}")->assertOk();
        }

        $this->get("/admin/turnamen/{$tournament->slug}/statistik?round={$round->id}")->assertOk();
    }

    public function test_standings_are_calculated_from_finished_matches(): void
    {
        $tournament = Tournament::factory()->create();
        $teams = collect(range(1, 4))->map(fn ($i) => Team::create([
            'tournament_id' => $tournament->id,
            'nama' => "Tim {$i}",
            'status_pendaftaran' => 'terdaftar',
        ]));

        $round = Round::create(['tournament_id' => $tournament->id, 'nomor' => 1, 'nama' => 'Grup Awal', 'tipe' => PhaseType::GRUP->value]);
        $group = Group::create(['tournament_id' => $tournament->id, 'round_id' => $round->id, 'nama' => 'Grup A', 'urutan' => 1]);
        $group->teams()->sync($teams->pluck('id'));

        MatchGame::create([
            'tournament_id' => $tournament->id, 'round_id' => $round->id, 'group_id' => $group->id,
            'tim_a_id' => $teams[0]->id, 'tim_b_id' => $teams[1]->id,
            'status' => 'selesai', 'skor_a' => 2, 'skor_b' => 0,
        ]);
        MatchGame::create([
            'tournament_id' => $tournament->id, 'round_id' => $round->id, 'group_id' => $group->id,
            'tim_a_id' => $teams[2]->id, 'tim_b_id' => $teams[3]->id,
            'status' => 'selesai', 'skor_a' => 1, 'skor_b' => 1,
        ]);

        $klasemen = app(KlasemenService::class)->klasemenGrup($group);

        $this->assertSame('Tim 1', $klasemen[0]['nama']);
        $this->assertSame(3, $klasemen[0]['poin']);
        $this->assertSame(2, $klasemen[0]['sg']);
        $this->assertSame(1, collect($klasemen)->firstWhere('nama', 'Tim 3')['poin']);
    }

    public function test_score_entry_and_progression_crown_a_champion(): void
    {
        $tournament = Tournament::factory()->create();
        $teams = collect(range(1, 4))->map(fn ($i) => Team::create([
            'tournament_id' => $tournament->id,
            'nama' => "Kesebelasan {$i}",
            'status_pendaftaran' => 'terdaftar',
        ]));

        $putaran = app(PutaranService::class);
        $scores = app(PertandinganService::class);

        $awal = $putaran->buatPutaran($tournament, 'Penyisihan', PhaseType::GRUP, 1);

        $semis = $putaran->siapkanFaseGugur($awal->fresh(), [
            ['tim_a' => $teams[0]->id, 'tim_b' => $teams[1]->id],
            ['tim_a' => $teams[2]->id, 'tim_b' => $teams[3]->id],
        ]);

        $this->assertSame('Semifinal', $semis->nama);

        foreach ($semis->matches as $index => $match) {
            $scores->ubahStatus($match, 'selesai');
            $scores->aturSkor($match, $index === 0 ? 1 : 0, $index === 0 ? 0 : 0);
            if ($index === 1) {
                $match->forceFill(['skor_penalti_a' => 4, 'skor_penalti_b' => 3])->saveQuietly();
                $scores->tentukanPemenang($match);
            }
        }

        $finalists = $semis->fresh()->matches->map(fn ($m) => $m->pemenang_id)->filter()->values();
        $this->assertCount(2, $finalists);

        $final = $putaran->siapkanFaseGugur($semis->fresh(), [
            ['tim_a' => $finalists[0], 'tim_b' => $finalists[1]],
        ]);

        $this->assertSame('Final', $final->nama);
        $this->assertSame(PhaseType::GUGUR, $final->tipe);

        $laga = $final->matches->first();
        $scores->ubahStatus($laga, 'selesai');
        $scores->aturSkor($laga, 3, 1);

        $this->assertSame($teams[0]->id, $putaran->juara($tournament->fresh())->id);
    }
}
