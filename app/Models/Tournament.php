<?php

namespace App\Models;

use App\TournamentStatus;
use Database\Factories\TournamentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable([
    'nama',
    'slug',
    'cabang_olahraga',
    'deskripsi',
    'tanggal_mulai',
    'tanggal_selesai',
    'lokasi',
    'status',
    'pengaturan_format',
])]
class Tournament extends Model
{
    /** @use HasFactory<TournamentFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'status' => TournamentStatus::class,
            'pengaturan_format' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Tournament $tournament) {
            if (empty($tournament->slug)) {
                $tournament->slug = Str::slug($tournament->nama);
                $count = 1;
                while (static::where('slug', $tournament->slug)->exists()) {
                    $tournament->slug = Str::slug($tournament->nama).'-'.$count++;
                }
            }
        });

        static::updating(function (Tournament $tournament) {
            if ($tournament->isDirty('nama') && empty($tournament->getOriginal('slug'))) {
                $tournament->slug = Str::slug($tournament->nama);
            } elseif ($tournament->isDirty('nama')) {
                // regenerate if needed
                $tournament->slug = Str::slug($tournament->nama);
                $count = 1;
                $id = $tournament->id;
                while (static::where('slug', $tournament->slug)->where('id', '!=', $id)->exists()) {
                    $tournament->slug = Str::slug($tournament->nama).'-'.$count++;
                }
            }
        });
    }
}
