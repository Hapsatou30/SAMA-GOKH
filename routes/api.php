<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\ProjetController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Route pour la modification et la suppression de projet.
Route::apiResource('projets', ProjetController::class)->only('destroy');
Route::post('projets/{projet}', [ProjetController::class, 'update']);

// Route pour vote
Route::apiResource('votes', VoteController::class)->only('store');

