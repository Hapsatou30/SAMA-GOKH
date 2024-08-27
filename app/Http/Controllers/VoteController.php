<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Vote;
use App\Traits\NotifiableTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests\StoreVoteRequest;
use App\Http\Requests\UpdateVoteRequest;

class VoteController extends Controller
{
    use NotifiableTrait; 
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Récupérer le nombre de votes ayant le statut 'pou' par projet
        $votesParProjet = Vote::select('projet_id', DB::raw('count(*) as total_votes'))
                              ->where('statut', 'pour')  // Filtrer par statut 'pou'
                              ->groupBy('projet_id')
                              ->get();
    
        // Retourner les données en format JSON avec un message de succès
        return response()->json([
            'message' => 'Nombre de votes (statut pou) par projet',
            'data' => $votesParProjet
        ], 200);
    }
    
    public function userVote($projetId, $userId)
    {
        // Récupérer le vote de l'utilisateur pour le projet spécifique
        $vote = Vote::where('projet_id', $projetId)
                    ->where('habitant_id', $userId)
                    ->first();
        
        // Retourner la réponse JSON
        return response()->json([
            'data' => $vote
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVoteRequest $request)
{
    // Récupérer l'utilisateur authentifié
    $user = Auth::user();
    $habitantId = $user->habitant->id;

    // Vérifier si l'utilisateur a déjà voté pour ce projet
    $existingVote = Vote::where('projet_id', $request->projet_id)
                         ->where('habitant_id', $habitantId)
                         ->first();

    if ($existingVote) {
        return response()->json([
            'message' => 'Vous avez déjà voté pour ce projet.',
        ], 400); // Utilisez un code de statut 400 pour indiquer une demande invalide
    }

    // Créer un nouveau vote avec l'ID de l'habitant connecté
    $vote = Vote::create(array_merge(
        $request->validated(),
        ['habitant_id' => $habitantId] // Ajouter l'ID de l'habitant
    ));

    // Utiliser le trait pour notifier tous les utilisateurs
    $this->notifyAllUsers($vote->projet_id, "Un nouveau vote a été ajouté.");

    // Retourner une réponse JSON avec un message de succès
    return response()->json([
        "message" => "Vote ajouté avec succès",
        "data" => $vote
    ], 201);
}

    

    

    /**
     * Display the specified resource.
     */
    public function show(Vote $vote)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vote $vote)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVoteRequest $request, Vote $vote)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vote $vote)
    {
        //
    }
}
