<?php

namespace App;

enum TournamentStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'aktif';
    case COMPLETED = 'selesai';
    case CANCELLED = 'dibatalkan';
}
