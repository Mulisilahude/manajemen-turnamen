<?php

namespace App\Models;

use App\Enums\MatchStatus;
use App\Enums\PhaseType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MatchGame extends Model
{
    protected $table = 'matches';

    protected $fillable = [
        'tournament_id',
        'round_id',
        'group_id',
        'nomor_pertandingan',
        'kode_pertandingan',
        'fase',
        'babak',
        'tim_a_id',
        'tim_b_id',
        'wasit_id',
        'tanggal',
        'jam',
        'lokasi',
        'status',
        'skor_a',
        'skor_b',
        'skor_penalti_a',
        'skor_penalti_b',
        'pemenang_id',
        'next_match_id',
        'next_slot',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'status' => MatchStatus::class,
            'fase' => PhaseType::class,
        ];
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function teamA(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'tim_a_id');
    }

    public function teamB(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'tim_b_id');
    }

    public function referee(): BelongsTo
    {
        return $this->belongsTo(Referee::class, 'wasit_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'pemenang_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(MatchEvent::class, 'match_id')->orderBy('menit');
    }

    public function goals(): HasMany
    {
        return $this->hasMany(MatchEvent::class, 'match_id')->where('tipe', 'gol');
    }

    public function nextMatch(): BelongsTo
    {
        return $this->belongsTo(self::class, 'next_match_id');
    }

    public function isSelesai(): bool
    {
        return $this->status === MatchStatus::SELESAI;
    }

    public function isGugur(): bool
    {
        return $this->fase === PhaseType::GUGUR;
    }

    public function babakLabel(): string
    {
        if ($this->babak) {
            return $this->babak;
        }

        return $this->round?->nama ?? 'Pertandingan';
    }

    public function skorLabel(): string
    {
        if ($this->isGugur() && $this->skor_penalti_a !== null && $this->skor_penalti_b !== null) {
            return $this->skor_a.' - '.$this->skor_b.' (pen '.$this->skor_penalti_a.'-'.$this->skor_penalti_b.')';
        }

        return $this->skor_a.' - '.$this->skor_b;
    }

    public function punyaSkor(): bool
    {
        return in_array($this->status?->value, [MatchStatus::BERLANGSUNG->value, MatchStatus::SELESAI->value], true);
    }

    public function teksSkor(): string
    {
        return $this->punyaSkor() ? $this->skor_a.' : '.$this->skor_b : '– : –';
    }
}
