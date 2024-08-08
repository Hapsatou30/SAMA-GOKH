<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjetController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Route::get('projets', [ProjetController::class, 'index']);
// Route::post('projet', [ProjetController::class, 'store']);
// Route::get('projets/{id}', [ProjetController::class, 'show']);
Route::apiResource('projets', ProjetController::class);












// use App\Http\Controllers\ProjetController;

// Route::prefix('projets')->group(function () {
//     Route::get('/', [ProjetController::class, 'index']);
//     Route::post('/', [ProjetController::class, 'store']);
//     Route::get('/{projet}', [ProjetController::class, 'show']);
//     Route::put('/{projet}', [ProjetController::class, 'update']);
//     Route::delete('/{projet}', [ProjetController::class, 'destroy']);
//     Route::get('/trashed', [ProjetController::class, 'trashed']);
//     Route::put('/restore/{projet}', [ProjetController::class, 'restore']);
//     Route::delete('/force-delete/{projet}', [ProjetController::class, 'forceDelete']);
// });

