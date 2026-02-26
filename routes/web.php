<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    // Players - protegido con middleware de permisos Spatie
    Route::middleware('permission:view player notes')->group(function () {
        Route::livewire('players', 'pages::players.index')
            ->name('players.index');

        Route::livewire('players/{player}/notes', 'pages::players.notes')
            ->name('players.notes');
    });
});

require __DIR__.'/settings.php';
