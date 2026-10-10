<?php

namespace App\Models;

use App\Enums\RegistrationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $fillable = [
        'tournament_id',
        'nama',
        'singkatan',
        'logo',
        'pelatih',
        'status_pendaftaran',
    ];

    protected function casts(): array
    {
        return [
            'status_pendaftaran' => RegistrationStatus::class,
        ];
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class);
    }

    public function matchesAsTeamA(): HasMany
    {
        return $this->hasMany(MatchGame::class, 'tim_a_id');
    }

    public function matchesAsTeamB(): HasMany
    {
        return $this->hasMany(MatchGame::class, 'tim_b_id');
    }
}
