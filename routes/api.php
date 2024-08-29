<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\ProjetController;
use App\Http\Controllers\Api\ApiController;

use App\Http\Controllers\HabitantController;
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
Route::get('/projets/municipalite/{municipaliteId}', [ProjetController::class, 'getProjetsByMunicipalite']);
Route::get('/projets/habitant/{id}', [ProjetController::class, 'getProjetsByHabitant']);
Route::put('/projets/{id}/etat', [ProjetController::class, 'updateEtat']);



    // Route pour les habitants
    Route::apiResource('habitants', HabitantController::class);
    Route::get('/habitants/commune/{municipaliteId}', [HabitantController::class, 'habitantsByCommune']);


// Route pour vote
Route::apiResource('votes', VoteController::class);
// Ajouter cette ligne dans le fichier routes/api.php
Route::get('votes/user/{projetId}/{userId}', [VoteController::class, 'userVote']);
Route::get('votes/contre', [VoteController::class, 'getVotesContre']);



//route pour les municipalities
// Route::apiResource('municipalites', MunicipaliteController::class);

// Route pour stocker une nouvelle municipalité
Route::post('/municipalites', [MunicipaliteController::class, 'store'])->name('municipalites.store');

// Route pour afficher une municipalité spécifique
Route::get('/municipalites/{municipalite}', [MunicipaliteController::class, 'show'])->name('municipalites.show');

// Route pour mettre à jour une municipalité spécifique
Route::put('/municipalites/{municipalite}', [MunicipaliteController::class, 'update'])->name('municipalites.update');

// Route pour supprimer une municipalité spécifique
Route::delete('/municipalites/{municipalite}', [MunicipaliteController::class, 'destroy'])->name('municipalites.destroy');

// Route pour les communes par région
Route::get('/municipalites/region/{region}', [MunicipaliteController::class, 'getCommunesByRegion']);
    

Route::get('/municipalite/connectee', [MunicipaliteController::class, 'getMunicipaliteConnectee']);


// Route pour les commentaires

Route::apiResource('commentaires', CommentaireController::class);

    // Route pour l'authentification pour l'habitant
    Route::get("profile", [ApiController::class, "profile"]);
    Route::get("refresh", [ApiController::class, "refreshToken"]);
    Route::get("logout", [ApiController::class, "logout"]);
});
// Route pour obtenir la liste des municipalités
Route::get('/municipalites', [MunicipaliteController::class, 'index'])->name('municipalites.index');

// Dans routes/api.php
Route::get('/municipalite/habitants', [MunicipaliteController::class, 'getHabitantsConnecte']);


// Route pour les notifications
Route::get('notifications', [NotificationController::class, 'getAllNotifications']);
Route::middleware('auth:sanctum')->post('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
Route::delete('/notifications/{id}', [NotificationController::class, 'deleteNotification']);
