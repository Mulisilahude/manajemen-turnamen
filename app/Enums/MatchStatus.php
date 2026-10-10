<?php

namespace App\Enums;

enum MatchStatus: string
{
    case DIJADWALKAN = 'dijadwalkan';
    case BERLANGSUNG = 'berlangsung';
    case SELESAI = 'selesai';
    case DITUNDA = 'ditunda';
    case DIBATALKAN = 'dibatalkan';

    public function label(): string
    {
        return match ($this) {
            self::DIJADWALKAN => 'Dijadwalkan',
            self::BERLANGSUNG => 'Berlangsung',
            self::SELESAI => 'Selesai',
            self::DITUNDA => 'Ditunda',
            self::DIBATALKAN => 'Dibatalkan',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::BERLANGSUNG => 'badge badge-primary',
            self::SELESAI => 'badge badge-success',
            default => 'badge badge-muted',
        };
    }
}
