<?php

namespace App\Enums;

enum RegistrationStatus: string
{
    case TERDAFTAR = 'terdaftar';
    case DIVERIFIKASI = 'diverifikasi';
    case DITOLAK = 'ditolak';

    public function label(): string
    {
        return match ($this) {
            self::TERDAFTAR => 'Terdaftar',
            self::DIVERIFIKASI => 'Diverifikasi',
            self::DITOLAK => 'Ditolak',
        };
    }
}
