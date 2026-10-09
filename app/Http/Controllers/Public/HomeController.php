<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $tournaments = Tournament::latest()->take(6)->get();

        return view('public.home', compact('tournaments'));
    }
}
