<?php

namespace Database\Factories;

use App\Models\Tournament;
use App\TournamentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tournament>
 */
class TournamentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nama = 'Turnamen '.$this->faker->city();

        return [
            'nama' => $nama,
            'slug' => Str::slug($nama).'-'.fake()->unique()->numberBetween(1000, 9999),
            'cabang_olahraga' => fake()->randomElement(['Futsal', 'Basket', 'Voli', 'Sepak Bola']),
            'deskripsi' => fake()->paragraph(2),
            'tanggal_mulai' => fake()->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'tanggal_selesai' => fake()->dateTimeBetween('+2 weeks', '+2 months')->format('Y-m-d'),
            'lokasi' => fake()->address(),
            'status' => TournamentStatus::ACTIVE->value,
            'pengaturan_format' => ['format' => 'pool', 'jumlah_tim' => 8],
        ];
    }
}
