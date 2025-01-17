<?php

use App\Http\Controllers\AskController;
use App\Http\Controllers\CustomInstructionController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');


    Route::get('/ask/{conversation}', [AskController::class, 'show'])->name('ask.show');
    Route::get('/ask', [AskController::class, 'index'])->name('ask.index');
    Route::post('/ask', [AskController::class, 'ask'])->name('ask.post');
    Route::get('/custom-instruction', [CustomInstructionController::class, 'index'])->name('customInstruction.index');
});
