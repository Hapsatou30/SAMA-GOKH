<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\ProjetController;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\CommentaireController;

use App\Http\Controllers\MunicipaliteController;


use App\Http\Controllers\NotificationController;


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

//route pour les municipalities
    Route::apiResource('municipalites', MunicipaliteController::class);
    Route::get('profile', [MunicipaliteController::class, 'profile']);

// Route pour les commentaires

Route::apiResource('commentaires', CommentaireController::class);

    // Route pour l'authentification pour l'habitant
    Route::get("profile", [ApiController::class, "profile"]);
    Route::get("refresh", [ApiController::class, "refreshToken"]);
    Route::get("logout", [ApiController::class, "logout"]);
});

// Route pour les notifications
Route::get('notifications', [NotificationController::class, 'getUserNotifications']);
Route::middleware('auth:sanctum')->post('notifications/{id}/read', [NotificationController::class, 'markAsRead']);


