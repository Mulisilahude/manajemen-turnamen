<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Referee extends Model
{
    protected $fillable = [
        'tournament_id',
        'nama',
        'nomor_kontak',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(MatchGame::class, 'wasit_id');
    }
}
