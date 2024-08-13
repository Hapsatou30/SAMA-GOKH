<?php

namespace App\Http\Controllers;


use App\Models\User;
use App\Models\Commentaire;
use App\Traits\NotifiableTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreCommentaireRequest;

use App\Http\Requests\UpdateCommentaireRequest;

class CommentaireController extends Controller
{
    use NotifiableTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Récupérer les commentaires groupés par projet
        $commentairesParProjet = Commentaire::with('habitant')
                                            ->orderBy('created_at', 'desc')
                                            ->get()
                                            ->groupBy('projet_id');
    
        // Retourner les données en format JSON avec un message de succès
        return response()->json([
            'message' => 'Liste des commentaires par projet',
            'data' => $commentairesParProjet
        ], 200);
    }
    

    /**
     * Show the form for creating a new resource.
     */


    
    public function store(StoreCommentaireRequest $request)
    {
        // Assurez-vous que l'utilisateur est authentifié
        $user = auth()->user();
    
        // Récupérer l'identifiant du habitant associé à l'utilisateur
        $habitantId = $user->habitant->id;
    
        // Ajouter 'habitant_id' au tableau des données validées
        $validatedData = $request->validated();
        $validatedData['habitant_id'] = $habitantId;
    
        // Créer le commentaire avec les données validées
        $commentaire = Commentaire::create($validatedData);
    
         // Vérification
    // dd($commentaire);
        // Utiliser le trait pour notifier tous les utilisateurs
        $this->notifyAllUsers($commentaire->projet_id, "Un nouveau commentaire a été ajouté : " . $commentaire->contenu);
    
        return response()->json([
            'message' => 'Commentaire ajouté avec succès!',
            'data' => $commentaire
        ], 201);
    }
    


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCommentaireRequest $request, Commentaire $commentaire)
{
    // Récupérer l'utilisateur authentifié
    $user = Auth::user();

    // Vérifier que l'habitant connecté est bien l'auteur du commentaire
    if ($user->habitant->id !== $commentaire->habitant_id) {
        return response()->json(['error' => 'Vous n\'avez pas l\'autorisation de modifier ce commentaire.'], 403);
    }

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
        // Récupérer l'utilisateur authentifié
        $user = Auth::user();
    
        // Vérifier que l'habitant connecté est bien l'auteur du commentaire
        if ($user->habitant->id !== $commentaire->habitant_id) {
            return response()->json(['error' => 'Vous n\'avez pas l\'autorisation de supprimer ce commentaire.'], 403);
        }
    
        // Supprimer le commentaire
        $commentaire->delete();
    
        // Retourner une réponse JSON avec un message de succès
        return response()->json([
            'message' => 'Commentaire supprimé avec succès'
        ], 200);
    }
    
}
