<?php

namespace App\Http\Controllers\Admin\Manage;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Services\StatistikService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatisticController extends Controller
{
    public function index(Request $request, Tournament $tournament, StatistikService $statistik): View
    {
        $roundId = $request->integer('round') ?: null;
        $rounds = $tournament->rounds()->orderBy('nomor')->get();

        $topSkor = $statistik->topSkor($tournament, $roundId);
        $topAssist = $statistik->topAssist($tournament, $roundId);
        $kartuKuning = $statistik->kartuKuning($tournament, $roundId);
        $kartuMerah = $statistik->kartuMerah($tournament, $roundId);

        return view('admin.manage.statistics.index', compact(
            'tournament', 'rounds', 'roundId', 'topSkor', 'topAssist', 'kartuKuning', 'kartuMerah'
        ));
    }
}
