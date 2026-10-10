<?php

namespace App;

enum TournamentStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'aktif';
    case COMPLETED = 'selesai';
    case CANCELLED = 'dibatalkan';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::ACTIVE => 'Aktif',
            self::COMPLETED => 'Selesai',
            self::CANCELLED => 'Dibatalkan',
        };
    }
}
