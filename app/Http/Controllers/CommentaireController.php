<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Commentaire;
use App\Http\Requests\StoreCommentaireRequest;
use App\Http\Requests\UpdateCommentaireRequest;

use App\Traits\NotifiableTrait;

class CommentaireController extends Controller
{
    use NotifiableTrait; 
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Récupérer tous les commentaires
    $commentaires = Commentaire::all();

    // Retourner les commentaires en format JSON avec un message de succès
    return response()->json([
        'message' => 'Liste des Commentaires',
        'data' => $commentaires
    ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCommentaireRequest $request)
    {
        $commentaire = Commentaire::create($request->validated());
    
        $users = User::all(); // Récupérer tous les utilisateurs
        foreach ($users as $user) {
            $this->addNotification($user->id, $commentaire->projet_id, "Un nouveau commentaire a été ajouté : " . $commentaire->contenu);
        }
    
        return response()->json(["message" => "Commentaire ajouté avec succès!", "data" => $commentaire], 201);
    }
    


   
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCommentaireRequest $request, Commentaire $commentaire)
    {
        // Valider les données reçues via la requête
    $validatedData = $request->validated();

    // Mettre à jour le commentaire avec les nouvelles données
    $commentaire->update($validatedData);

    // Retourner une réponse JSON avec un message de succès
    return response()->json([
        'message' => 'Commentaire mis à jour avec succès',
        'data' => $commentaire
    ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Commentaire $commentaire)
    {
        // Supprimer le commentaire
        $commentaire->delete();

        // Retourner une réponse JSON avec un message de succès
        return response()->json([
            'message' => 'Commentaire supprimé avec succès'
        ], 200);
    }
}
