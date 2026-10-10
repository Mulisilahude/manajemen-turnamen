<?php

namespace App\Models;

use App\Enums\EventType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchEvent extends Model
{
    protected $fillable = [
        'tournament_id',
        'match_id',
        'team_id',
        'player_id',
        'assist_player_id',
        'tipe',
        'menit',
        'detail',
    ];

    protected function casts(): array
    {
        return [
            'tipe' => EventType::class,
        ];
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(MatchGame::class, 'match_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function assistPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'assist_player_id');
    }
}
