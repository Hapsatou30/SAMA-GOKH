<?php

namespace App\Http\Controllers;

use App\Mail\Email;
use App\Models\User;
use App\Models\Projet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\StoreProjetRequest;
use App\Http\Requests\UpdateProjetRequest;

use App\Traits\NotifiableTrait;

class ProjetController extends Controller
{
    use NotifiableTrait; 
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projets = Projet::all();
        return response()->json([
            'status' => true,
            'message' => 'la liste des projets',
            'data' => $projets
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    
     public function store(StoreProjetRequest $request)
     {
         // Créer une nouvelle instance du projet
         $projet = new Projet();
         
         // Assigner l'ID de l'utilisateur connecté au projet
         $projet->user_id = Auth::id(); // Utiliser Auth::id() pour obtenir l'ID de l'utilisateur connecté
         
         // Remplir les autres attributs du projet avec les données validées
         $projet->fill($request->validated());
         
         // Sauvegarder le projet
         $projet->save();
     
         // Obtenir l'utilisateur connecté
         $user = Auth::user();
     
         if ($user && $user->role_id === 3) { // Vérification basée sur role_id
             $habitant = $user->habitant;
             Mail::to($user->email)->send(new Email($habitant, $projet->nom));
         }
     
          // Utiliser le trait pour notifier tous les utilisateurs
        $this->notifyAllUsers($projet->id, "Un nouveau projet a été ajouté : " . $projet->nom);   
     
         return self::customJsonResponse("Projet créé avec succès", $projet, 201);
     }
     
    
    

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Récupérer le projet avec ses commentaires
        $projet = Projet::with('commentaires','votes')->findOrFail($id);
    
        // Retourner les détails du projet avec les commentaires
        return response()->json($projet);
    }
    

    /**
     * Show the form for editing the specified resource.
     */
  
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjetRequest $request, Projet $projet)
    {
        // Vérifier que l'utilisateur courant est bien le propriétaire du projet
        if (Auth::id() !== $projet->user_id) {
            return response()->json(['error' => 'Vous n\'avez pas l\'autorisation de modifier ce projet.'], 403);
        }
    
        // Mettre à jour les informations du projet
        $projet->fill($request->validated());
        $projet->save(); // Utiliser save() plutôt que update() après fill()
    
        return $this->customJsonResponse("Projet modifié avec succès", $projet);
    }
    
    /**
     * Remove the specified resource from storage.
     */
   
     public function destroy(Projet $projet)
     {
         // Vérifier que l'utilisateur courant est bien le propriétaire du projet
         if (Auth::id() !== $projet->user_id) {
             return response()->json(['error' => 'Vous n\'avez pas l\'autorisation de supprimer ce projet.'], 403);
         }
     
         // Utiliser le trait pour notifier tous les utilisateurs
    $this->notifyAllUsers($projet->id, "Le projet suivant a été supprimé : " . $projet->nom);
     
         // Supprimer le projet
         $projet->delete();
     
         return response()->json(["message" => "Projet supprimé avec succès"], 204);
     }
     
    
    

    
}
