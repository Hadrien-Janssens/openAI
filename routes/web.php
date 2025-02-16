<?php

use App\Http\Controllers\AskController;
use App\Http\Controllers\CustomInstructionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::get('/register', function () {
    return Inertia::render('Auth/Register');
})->name('register');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    Route::get('/', [AskController::class, 'index'])->name('ask.index');
    Route::post('/create', [AskController::class, 'create'])->name('ask.create');
    Route::post('/update-title', [AskController::class, 'updateTitle'])->name('ask.updateTitle');
    Route::get('/conversation/{conversation}', [AskController::class, 'show'])->name('ask.show');
    Route::delete('/conversation/{conversation}', [AskController::class, 'destroy'])->name('ask.destroy');
    Route::delete('/delete-two-last-messages/{conversation}', [AskController::class, 'deleteTwoLastMessages'])->name('ask.deleteTwoLastMessages');
    Route::get('/conversation', [AskController::class, 'conversation'])->name('conversation.index');
    Route::post('/ask', [AskController::class, 'streamMessage'])->name('ask.post');
    Route::get('/custom-instruction', [CustomInstructionController::class, 'index'])->name('customInstruction.index');
    Route::post('/custom-instruction', [CustomInstructionController::class, 'store'])->name('customInstruction.index');
});