<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Manage\DashboardController as ManageDashboardController;
use App\Http\Controllers\Admin\Manage\GroupController;
use App\Http\Controllers\Admin\Manage\JadwalController;
use App\Http\Controllers\Admin\Manage\MatchController;
use App\Http\Controllers\Admin\Manage\MatchEventController;
use App\Http\Controllers\Admin\Manage\OfficialImportController;
use App\Http\Controllers\Admin\Manage\PlayerController;
use App\Http\Controllers\Admin\Manage\RefereeController;
use App\Http\Controllers\Admin\Manage\RoundController;
use App\Http\Controllers\Admin\Manage\StatisticController;
use App\Http\Controllers\Admin\Manage\TeamController;
use App\Http\Controllers\Admin\TournamentController as AdminTournamentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\TournamentController as PublicTournamentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::prefix('turnamen')->name('public.tournaments.')->group(function () {
    Route::get('/', [PublicTournamentController::class, 'index'])->name('index');
    Route::get('/{tournament}', [PublicTournamentController::class, 'show'])->name('show');
    Route::get('/{tournament}/tim', [PublicTournamentController::class, 'teams'])->name('teams');
    Route::get('/{tournament}/klasemen', [PublicTournamentController::class, 'standings'])->name('standings');
    Route::get('/{tournament}/jadwal', [PublicTournamentController::class, 'schedule'])->name('schedule');
    Route::get('/{tournament}/jadwal/skor', [PublicTournamentController::class, 'liveScores'])->name('schedule.live');
    Route::get('/{tournament}/hasil', [PublicTournamentController::class, 'results'])->name('results');
    Route::get('/{tournament}/statistik', [PublicTournamentController::class, 'statistics'])->name('statistics');
    Route::get('/{tournament}/bagan', [PublicTournamentController::class, 'bracket'])->name('bracket');
    Route::get('/{tournament}/pertandingan/{match}', [PublicTournamentController::class, 'match'])->name('match');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('tournaments', AdminTournamentController::class);
    Route::post('/tournaments/{tournament}/activate', [AdminTournamentController::class, 'setActive'])->name('tournaments.activate');

    Route::prefix('turnamen/{tournament}')->name('manage.')->group(function () {
        Route::get('/', [ManageDashboardController::class, 'index'])->name('dashboard');

        Route::get('impor', [OfficialImportController::class, 'index'])->name('impor.index');
        Route::post('impor/pratinjau', [OfficialImportController::class, 'pratinjau'])->name('impor.pratinjau');
        Route::post('impor/simpan', [OfficialImportController::class, 'simpan'])->name('impor.simpan');
        Route::post('impor/reset', [OfficialImportController::class, 'reset'])->name('impor.reset');

        Route::get('tim', [TeamController::class, 'index'])->name('teams.index');
        Route::post('tim', [TeamController::class, 'store'])->name('teams.store');
        Route::put('tim/{team}', [TeamController::class, 'update'])->name('teams.update');
        Route::delete('tim/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');

        Route::get('pemain', [PlayerController::class, 'index'])->name('players.index');
        Route::post('pemain', [PlayerController::class, 'store'])->name('players.store');
        Route::put('pemain/{player}', [PlayerController::class, 'update'])->name('players.update');
        Route::delete('pemain/{player}', [PlayerController::class, 'destroy'])->name('players.destroy');

        Route::get('wasit', [RefereeController::class, 'index'])->name('referees.index');
        Route::post('wasit', [RefereeController::class, 'store'])->name('referees.store');
        Route::put('wasit/{referee}', [RefereeController::class, 'update'])->name('referees.update');
        Route::delete('wasit/{referee}', [RefereeController::class, 'destroy'])->name('referees.destroy');

        Route::get('putaran', [RoundController::class, 'index'])->name('rounds.index');
        Route::post('putaran', [RoundController::class, 'store'])->name('rounds.store');
        Route::delete('putaran/{round}', [RoundController::class, 'destroy'])->name('rounds.destroy');
        Route::get('putaran/{round}/lanjut', [RoundController::class, 'lanjut'])->name('rounds.lanjut');
        Route::post('putaran/{round}/lanjut-grup', [RoundController::class, 'lanjutGrup'])->name('rounds.lanjut-grup');
        Route::post('putaran/{round}/lanjut-gugur', [RoundController::class, 'lanjutGugur'])->name('rounds.lanjut-gugur');
        Route::post('putaran/{round}/grup', [RoundController::class, 'storeGroup'])->name('groups.store');
        Route::post('grup/{group}/isi', [RoundController::class, 'isiGroup'])->name('groups.isi');

        Route::get('putaran/{round}/grup', [GroupController::class, 'kelola'])->name('groups.kelola');
        Route::post('putaran/{round}/grup-simpan', [GroupController::class, 'simpan'])->name('groups.simpan');
        Route::get('putaran/{round}/undian', [GroupController::class, 'undian'])->name('groups.undian');
        Route::post('putaran/{round}/undian/proses', [GroupController::class, 'undianProses'])->name('groups.undian.proses');
        Route::post('putaran/{round}/undian/sahkan', [GroupController::class, 'undianSahkan'])->name('groups.undian.sahkan');
        Route::post('putaran/{round}/undian/batal', [GroupController::class, 'undianBatal'])->name('groups.undian.batal');

        Route::get('putaran/{round}/jadwal', [JadwalController::class, 'index'])->name('jadwal.index');
        Route::post('putaran/{round}/jadwal/proses', [JadwalController::class, 'proses'])->name('jadwal.proses');
        Route::post('putaran/{round}/jadwal/sahkan', [JadwalController::class, 'sahkan'])->name('jadwal.sahkan');
        Route::post('putaran/{round}/jadwal/batal', [JadwalController::class, 'batal'])->name('jadwal.batal');

        Route::get('pertandingan', [MatchController::class, 'index'])->name('matches.index');
        Route::get('pertandingan/baru', [MatchController::class, 'form'])->name('matches.create');
        Route::post('pertandingan', [MatchController::class, 'store'])->name('matches.store');
        Route::get('pertandingan/{match}/edit', [MatchController::class, 'form'])->name('matches.edit');
        Route::put('pertandingan/{match}', [MatchController::class, 'update'])->name('matches.update');
        Route::delete('pertandingan/{match}', [MatchController::class, 'destroy'])->name('matches.destroy');
        Route::post('pertandingan/{match}/skor', [MatchController::class, 'aturSkor'])->name('matches.skor');
        Route::post('pertandingan/{match}/status', [MatchController::class, 'ubahStatus'])->name('matches.status');
        Route::post('pertandingan/{match}/kejadian', [MatchEventController::class, 'store'])->name('events.store');
        Route::delete('pertandingan/{match}/kejadian/{event}', [MatchEventController::class, 'destroy'])->name('events.destroy');

        Route::get('statistik', [StatisticController::class, 'index'])->name('statistics.index');
    });
});
