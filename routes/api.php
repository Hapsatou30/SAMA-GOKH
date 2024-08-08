<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\VoteController;
use App\Http\Controllers\ProjetController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');




// Route pour les projets.
Route::apiResource('projets', ProjetController::class);

// Route pour vote
Route::apiResource('votes', VoteController::class)->only('store');
