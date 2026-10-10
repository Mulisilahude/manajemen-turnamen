<?php

namespace App\Models;

use App\TournamentStatus;
use Database\Factories\TournamentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
    'tampil_publik',
    'pengaturan_format',
])]
class Tournament extends Model
{
    /** @use HasFactory<TournamentFactory> */
    use HasFactory;

    public const DEFAULT_FORMAT = [
        'poin_menang' => 3,
        'poin_seri' => 1,
        'poin_kalah' => 0,
        'tim_per_grup' => 4,
        'jumlah_grup_pertama' => 2,
        'pemecah_seri' => 'selisih_gol',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'status' => TournamentStatus::class,
            'tampil_publik' => 'boolean',
            'pengaturan_format' => 'array',
        ];
    }

    /**
     * Status turnamen yang boleh dilihat pengunjung publik (terbit).
     */
    public const STATUS_TERBIT = [
        TournamentStatus::ACTIVE,
        TournamentStatus::COMPLETED,
    ];

    public function format(): array
    {
        return array_merge(self::DEFAULT_FORMAT, $this->pengaturan_format ?? []);
    }

    /**
     * Batasi kueri ke turnamen yang boleh dilihat publik: sudah diaktifkan
     * untuk tampil (`tampil_publik`) dan berstatus aktif/selesai. Draft dan
     * turnamen dibatalkan tidak pernah muncul di halaman publik.
     */
    public function scopeTerbit(Builder $query): Builder
    {
        return $query
            ->where('tampil_publik', true)
            ->whereIn('status', array_map(fn (TournamentStatus $status) => $status->value, self::STATUS_TERBIT));
    }

    public function sudahTerbit(): bool
    {
        return (bool) $this->tampil_publik
            && $this->status !== null
            && in_array($this->status, self::STATUS_TERBIT, true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function referees(): HasMany
    {
        return $this->hasMany(Referee::class);
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(Round::class)->orderBy('nomor');
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(MatchGame::class);
    }

    public function matchEvents(): HasMany
    {
        return $this->hasMany(MatchEvent::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class)->latest();
    }

    protected static function booted(): void
    {
        static::creating(function (Tournament $tournament) {
            if (empty($tournament->slug)) {
                $tournament->slug = static::uniqueSlug($tournament->nama);
            }
        });

        static::updating(function (Tournament $tournament) {
            if ($tournament->isDirty('nama')) {
                $tournament->slug = static::uniqueSlug($tournament->nama, $tournament->id);
            }
        });
    }

    protected static function uniqueSlug(string $name, ?int $excludeId = null): string
    {
        $base = Str::slug($name) ?: 'turnamen';
        $slug = $base;
        $count = 1;

        while (static::where('slug', $slug)->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))->exists()) {
            $slug = $base.'-'.$count++;
        }

        return $slug;
    }
}
