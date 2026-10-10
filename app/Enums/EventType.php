<?php

namespace App\Enums;

enum EventType: string
{
    case GOL = 'gol';
    case KARTU_KUNING = 'kartu_kuning';
    case KARTU_MERAH = 'kartu_merah';

    public function label(): string
    {
        return match ($this) {
            self::GOL => 'Gol',
            self::KARTU_KUNING => 'Kartu Kuning',
            self::KARTU_MERAH => 'Kartu Merah',
        };
    }
}
