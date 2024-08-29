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

    public function index()
    {
        $votesParProjet = Vote::select('projet_id', DB::raw('count(*) as total_votes'))
                              ->where('statut', 'pour')
                              ->groupBy('projet_id')
                              ->get();
    
        return response()->json([
            'message' => 'Nombre de votes (statut pour) par projet',
            'data' => $votesParProjet
        ], 200);
    }

    public function getVotesContre()
    {
        $votesParProjet = Vote::select('projet_id', DB::raw('count(*) as total_votes'))
                              ->where('statut', 'contre')
                              ->groupBy('projet_id')
                              ->get();
        
        return response()->json([
            'message' => 'Nombre de votes (statut contre) par projet',
            'data' => $votesParProjet
        ], 200);
    }
    
    public function userVote($projetId, $userId)
    {
        $vote = Vote::where('projet_id', $projetId)
                    ->where('habitant_id', $userId)
                    ->first();
        
        return response()->json([
            'data' => $vote
        ], 200);
    }

    public function store(StoreVoteRequest $request)
    {
        $user = Auth::user();
        $habitantId = $user->habitant->id;

        // Vérifier si l'utilisateur a déjà voté pour ce projet
        $existingVote = Vote::where('projet_id', $request->projet_id)
                             ->where('habitant_id', $habitantId)
                             ->first();

        if ($existingVote) {
            return response()->json([
                'message' => 'Vous avez déjà voté pour ce projet.',
            ], 400);
        }

        // Créer un nouveau vote avec l'ID de l'habitant connecté
        $vote = Vote::create(array_merge(
            $request->validated(),
            ['habitant_id' => $habitantId]
        ));

        // Notifier tous les utilisateurs
        $this->notifyAllUsers($vote->projet_id, "Un nouveau vote a été ajouté sur le projet " . $vote->projet->nom);

        return response()->json([
            "message" => "Vote ajouté avec succès",
            "data" => $vote
        ], 201);
    }
}
