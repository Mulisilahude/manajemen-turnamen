<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use Illuminate\View\View;

class TournamentController extends Controller
{
    public function index(): View
    {
        $tournaments = Tournament::latest()->paginate(12);

        return view('public.tournaments.index', compact('tournaments'));
    }

    public function show(Tournament $tournament): View
    {
        return view('public.tournaments.show', compact('tournament'));
    }
}
