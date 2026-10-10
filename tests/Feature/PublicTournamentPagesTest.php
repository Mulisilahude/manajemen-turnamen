<?php

namespace Tests\Feature;

use App\Models\Tournament;
use App\Models\User;
use App\TournamentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PublicTournamentPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_public_tournament_pages_render(): void
    {
        $tournament = Tournament::factory()->create();

        foreach ([
            '/turnamen',
            "/turnamen/{$tournament->slug}",
            "/turnamen/{$tournament->slug}/tim",
            "/turnamen/{$tournament->slug}/klasemen",
            "/turnamen/{$tournament->slug}/jadwal",
            "/turnamen/{$tournament->slug}/hasil",
            "/turnamen/{$tournament->slug}/statistik",
            "/turnamen/{$tournament->slug}/bagan",
        ] as $url) {
            $this->get($url)->assertStatus(200);
        }
    }

    public function test_draft_tournament_is_hidden_from_public(): void
    {
        $draft = Tournament::factory()->create(['status' => TournamentStatus::DRAFT, 'tampil_publik' => false, 'nama' => 'Turnamen Rahasia']);

        $this->get('/turnamen')->assertStatus(200)->assertDontSee('Turnamen Rahasia');
        $this->get("/turnamen/{$draft->slug}")->assertStatus(404);
        $this->get("/turnamen/{$draft->slug}/jadwal")->assertStatus(404);
        $this->get("/turnamen/{$draft->slug}/klasemen")->assertStatus(404);
    }

    public function test_draft_tournament_is_hidden_even_when_public_flag_is_enabled(): void
    {
        $draft = Tournament::factory()->create([
            'status' => TournamentStatus::DRAFT,
            'tampil_publik' => true,
            'nama' => 'Draf Belum Terbit',
        ]);

        $this->get('/')->assertStatus(200)->assertDontSee('Draf Belum Terbit');
        $this->get('/turnamen')->assertStatus(200)->assertDontSee('Draf Belum Terbit');
        $this->get("/turnamen/{$draft->slug}")->assertStatus(404);
        $this->get("/turnamen/{$draft->slug}/jadwal")->assertStatus(404);
        $this->getJson("/turnamen/{$draft->slug}/jadwal/skor")->assertStatus(404);
    }

    public function test_published_tournament_appears_on_home(): void
    {
        $published = Tournament::factory()->create([
            'status' => TournamentStatus::ACTIVE,
            'tampil_publik' => true,
            'nama' => 'Turnamen Terbit Resmi',
        ]);

        $this->get('/')->assertStatus(200)->assertSee('Turnamen Terbit Resmi');
        $this->get('/turnamen')->assertStatus(200)->assertSee('Turnamen Terbit Resmi');
        $this->get("/turnamen/{$published->slug}")->assertStatus(200);
    }

    public function test_admin_can_preview_draft_tournament(): void
    {
        $draft = Tournament::factory()->create(['status' => TournamentStatus::DRAFT, 'tampil_publik' => false, 'nama' => 'Turnamen Rahasia']);

        $this->actingAs($this->admin())
            ->get("/turnamen/{$draft->slug}")
            ->assertStatus(200)
            ->assertSee('Pratinjau Admin')
            ->assertSee('Turnamen Rahasia');
    }

    /**
     * @return array{0: Tournament, 1: callable}
     */
    protected function fixture(): array
    {
        $tournament = Tournament::factory()->create();
        $round = $tournament->rounds()->create(['nomor' => 1, 'nama' => 'Fase Grup', 'tipe' => 'grup']);
        $group = $round->groups()->create(['tournament_id' => $tournament->id, 'nama' => 'Grup L', 'urutan' => 1]);
        $timA = $tournament->teams()->create(['nama' => 'MOLOPATODU FC', 'status_pendaftaran' => 'terdaftar']);
        $timB = $tournament->teams()->create(['nama' => 'WASENG FC', 'singkatan' => 'WSG', 'status_pendaftaran' => 'terdaftar']);

        $buat = fn (array $atribut) => $tournament->matches()->create(array_merge([
            'round_id' => $round->id,
            'group_id' => $group->id,
            'nomor_pertandingan' => 1,
            'tim_a_id' => $timA->id,
            'tim_b_id' => $timB->id,
            'tanggal' => '2026-10-10',
            'jam' => '20:15:00',
        ], $atribut));

        return [$tournament, $buat];
    }

    public function test_schedule_card_renders_each_status(): void
    {
        [$tournament, $buat] = $this->fixture();

        $hari = Carbon::parse('2026-10-10')->locale('id')->translatedFormat('l, d F Y');

        $dijadwalkan = $tournament->matches()->find($buat(['status' => 'dijadwalkan', 'skor_a' => 0, 'skor_b' => 0])->id);
        $berlangsung = $tournament->matches()->find($buat(['status' => 'berlangsung', 'skor_a' => 2, 'skor_b' => 1])->id);
        $selesai = $tournament->matches()->find($buat(['status' => 'selesai', 'skor_a' => 3, 'skor_b' => 0])->id);

        $render = fn ($match) => view('public.tournaments.partials.match-card', [
            'tournament' => $tournament,
            'match' => $match,
        ])->render();

        $kartuDijadwalkan = $render($dijadwalkan);
        $this->assertStringContainsString($hari, $kartuDijadwalkan);
        $this->assertStringContainsString('20.15 WITA', $kartuDijadwalkan);
        $this->assertStringContainsString('Putaran 1 · Grup L', $kartuDijadwalkan);
        $this->assertStringContainsString('– : –', $kartuDijadwalkan);
        $this->assertStringContainsString('Dijadwalkan', $kartuDijadwalkan);
        $this->assertStringNotContainsString('>0 : 0<', $kartuDijadwalkan);

        $kartuBerlangsung = $render($berlangsung);
        $this->assertStringContainsString('2 : 1', $kartuBerlangsung);
        $this->assertStringContainsString('Berlangsung', $kartuBerlangsung);
        $this->assertSame(1, substr_count($kartuBerlangsung, 'data-match-field="score"'), 'Skor hanya dirender sekali (kolom kanan).');
        $this->assertStringNotContainsString('hidden sm:inline-flex', $kartuBerlangsung);

        $kartuSelesai = $render($selesai);
        $this->assertStringContainsString('3 : 0', $kartuSelesai);
        $this->assertStringContainsString('Selesai', $kartuSelesai);
    }

    public function test_schedule_card_shows_initials_for_long_names(): void
    {
        [$tournament, $buat] = $this->fixture();

        $match = $tournament->matches()->find($buat(['status' => 'dijadwalkan'])->id);

        $html = view('public.tournaments.partials.match-card', [
            'tournament' => $tournament,
            'match' => $match,
        ])->render();

        $this->assertStringContainsString('MOLOPATODU FC', $html);
        $this->assertStringContainsString('>MF<', $html);
        $this->assertStringContainsString('>WSG<', $html);
    }

    public function test_schedule_page_exposes_polling_endpoint(): void
    {
        [$tournament, $buat] = $this->fixture();
        $buat(['status' => 'berlangsung', 'skor_a' => 1, 'skor_b' => 0]);

        $this->get("/turnamen/{$tournament->slug}/jadwal")
            ->assertOk()
            ->assertSee('data-match-endpoint', false)
            ->assertSee('data-match-card', false)
            ->assertSee('data-match-status="berlangsung"', false);
    }

    public function test_live_scores_endpoint_returns_minimal_payload(): void
    {
        [$tournament, $buat] = $this->fixture();
        $berlangsung = $buat(['status' => 'berlangsung', 'skor_a' => 2, 'skor_b' => 1]);
        $buat(['status' => 'dijadwalkan']);

        $this->getJson("/turnamen/{$tournament->slug}/jadwal/skor")
            ->assertOk()
            ->assertJsonCount(2, 'matches')
            ->assertJsonFragment([
                'id' => $berlangsung->id,
                'status' => 'berlangsung',
                'label' => 'Berlangsung',
                'badge' => 'badge badge-primary',
                'skor' => '2 : 1',
                'ada_skor' => true,
            ])
            ->assertJsonFragment(['skor' => '– : –', 'ada_skor' => false]);
    }

    public function test_live_scores_endpoint_hidden_for_draft(): void
    {
        $draft = Tournament::factory()->create(['status' => TournamentStatus::DRAFT, 'tampil_publik' => false]);

        $this->getJson("/turnamen/{$draft->slug}/jadwal/skor")->assertStatus(404);
    }
}
