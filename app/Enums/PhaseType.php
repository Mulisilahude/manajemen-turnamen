<?php

namespace App\Enums;

enum PhaseType: string
{
    case GRUP = 'grup';
    case GUGUR = 'gugur';

    public function label(): string
    {
        return match ($this) {
            self::GRUP => 'Fase Grup',
            self::GUGUR => 'Fase Gugur',
        };
    }
}
