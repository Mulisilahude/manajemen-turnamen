<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TournamentController as AdminTournamentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\TournamentController as PublicTournamentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/turnamen', [PublicTournamentController::class, 'index'])->name('public.tournaments.index');
Route::get('/turnamen/{tournament:slug}', [PublicTournamentController::class, 'show'])->name('public.tournaments.show');

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Admin
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('tournaments', AdminTournamentController::class);
    Route::post('/tournaments/{tournament}/activate', [AdminTournamentController::class, 'setActive'])->name('tournaments.activate');
});
