<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\VoteController;
use App\Http\Controllers\ProjetController;

use App\Http\Controllers\CommentaireController;


use App\Http\Controllers\Api\ApiController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::post("register", [ApiController::class, "register"]);
Route::post("login", [ApiController::class, "login"]);

Route::group([
    "middleware" => ["auth"]
], function(){
    // Route pour les projets.
Route::apiResource('projets', ProjetController::class);

// Route pour vote
Route::apiResource('votes', VoteController::class)->only('store');

Route::apiResource('commentaires', CommentaireController::class);

    Route::get("profile", [ApiController::class, "profile"]);
    Route::get("refresh", [ApiController::class, "refreshToken"]);
    Route::get("logout", [ApiController::class, "logout"]);
});
