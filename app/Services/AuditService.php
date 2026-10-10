<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Tournament;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    public function log(?Tournament $tournament, string $aksi, string $entitas, ?int $entitasId = null, ?string $deskripsi = null, ?array $data = null): AuditLog
    {
        return AuditLog::create([
            'tournament_id' => $tournament?->id,
            'user_id' => Auth::id(),
            'aksi' => $aksi,
            'entitas' => $entitas,
            'entitas_id' => $entitasId,
            'deskripsi' => $deskripsi,
            'data' => $data,
        ]);
    }
}
