<?php

namespace App\Models;

use App\Enums\PhaseType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Round extends Model
{
    protected $fillable = [
        'tournament_id',
        'parent_round_id',
        'nomor',
        'nama',
        'tipe',
        'lanjutan_dibuat',
    ];

    protected function casts(): array
    {
        return [
            'tipe' => PhaseType::class,
            'lanjutan_dibuat' => 'boolean',
        ];
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class)->orderBy('urutan');
    }

    public function matches(): HasMany
    {
        return $this->hasMany(MatchGame::class, 'round_id')->orderBy('nomor_pertandingan');
    }

    public function isGrup(): bool
    {
        return $this->tipe === PhaseType::GRUP;
    }
}
