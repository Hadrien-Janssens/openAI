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
    // Route::get('/dashboard', function () {
    //     return Inertia::render('Dashboard');
    // })->name('dashboard');


    Route::get('/', [AskController::class, 'index'])->name('ask.index');
    Route::post('/create', [AskController::class, 'create'])->name('ask.create');
    Route::post('/update-title', [AskController::class, 'updateTitle'])->name('ask.updateTitle');
    Route::get('/{conversation}', [AskController::class, 'show'])->name('ask.show');
    Route::delete('/conversation/{conversation}', [AskController::class, 'destroy'])->name('ask.destroy');
    Route::post('/ask', [AskController::class, 'streamMessage'])->name('ask.post');
    Route::get('/custom-instruction', [CustomInstructionController::class, 'index'])->name('customInstruction.index');
    Route::post('/custom-instruction/about', [CustomInstructionController::class, 'aboutInstructions'])->name('customInstruction.aboutInstructions');
    Route::post('/custom-instruction/comportement', [CustomInstructionController::class, 'comportementInstructions'])->name('customInstruction.comportementInstructions');
});
