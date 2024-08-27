<?php

namespace App\Http\Controllers;

use App\Mail\Email;
use App\Models\User;
use App\Models\Projet;
use App\Models\Municipalite;
use App\Traits\NotifiableTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreProjetRequest;
use App\Models\Habitant;
use App\Http\Requests\UpdateProjetRequest;

class ProjetController extends Controller
{
    use NotifiableTrait; 
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtenir l'utilisateur connecté
        $user = auth()->user();
    
        // Vérifier si l'utilisateur a une municipalité associée
        $municipalite = $user->municipalites;
    
        if (!$municipalite) {
            return response()->json([
                'status' => false,
                'message' => 'L\'utilisateur connecté n\'est pas associé à une municipalité.',
            ], 403);
        }
    
        // Récupérer l'ID de la municipalité de l'utilisateur connecté
        $municipaliteId = $municipalite->id;
    
        // Récupérer les IDs des utilisateurs qui sont des habitants de cette municipalité
        $habitantUserIds = Habitant::where('municipalite_id', $municipaliteId)->pluck('user_id');
    
        // Inclure également les projets créés par la municipalité
        $projets = Projet::whereIn('user_id', $habitantUserIds)
                         ->orWhere('user_id', $municipalite->user_id)
                         ->get();
    
        // Retourner les projets trouvés avec une réponse 200
        return response()->json([
            'status' => true,
            'message' => 'La liste des projets pour la municipalité connectée',
            'data' => $projets
        ]);
    }
    
    
    public function getProjetsByMunicipalite($municipaliteId)
{
    try {
        // Récupérer la municipalité en fonction de l'ID fourni
        $municipalite = Municipalite::findOrFail($municipaliteId);

        // Récupérer les IDs des utilisateurs qui sont des habitants de cette municipalité
        $habitantUserIds = $municipalite->habitants()->pluck('user_id');

        // Récupérer tous les projets qui appartiennent soit aux habitants de la municipalité,
        // soit à la municipalité elle-même
        $projets = Projet::whereIn('user_id', $habitantUserIds) // Projets créés par les habitants
                         ->orWhere('user_id', $municipalite->user_id) // Projets créés par la municipalité
                         ->get();

        // Vérifier si des projets ont été trouvés
        if ($projets->isEmpty()) {
            // Retourner une réponse 404 si aucun projet n'est trouvé
            return response()->json(['message' => 'Aucun projet trouvé pour cette municipalité'], 404);
        }

        // Retourner les projets trouvés avec une réponse 200
        return response()->json(['data' => $projets], 200);
    } catch (\Exception $e) {
        // En cas d'erreur, loguer l'erreur pour débogage
        Log::error('Erreur lors de la récupération des projets par municipalité: ' . $e->getMessage());

        // Retourner une réponse 500 avec un message générique
        return response()->json(['error' => 'Erreur serveur, veuillez réessayer plus tard.'], 500);
    }
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
    $projet->fill($request->except('photo')); // Exclure la photo des données à remplir

    // Vérifier si un fichier a été envoyé
    if ($request->hasFile('photo')) {
        $photo = $request->file('photo');
        
        // Générer un nom unique pour le fichier
        $photoName = time() . '_' . $photo->getClientOriginalName();
        
        // Déplacer le fichier dans le répertoire de stockage
        $photo->storeAs('public/photos', $photoName);
        
        // Sauvegarder le nom du fichier dans le champ approprié du modèle
        $projet->photo = $photoName;
    }
    
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

    
        public function getProjetsByMunicipaliteForConnectedHabitant()
    {
        // Récupérer l'habitant connecté
        $habitant = Auth::user()->habitant;

        if (!$habitant) {
            return response()->json([
                'status' => false,
                'message' => 'Habitant non trouvé pour l\'utilisateur connecté.',
            ], 404);
        }

        // Récupérer les projets associés à la municipalité de l'habitant
        $projets = Projet::where('municipalite_id', $habitant->municipalite_id)->get();

        return response()->json([
            'status' => true,
            'message' => 'Liste des projets récupérée avec succès.',
            'data' => $projets
        ]);
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
        $projet->fill($request->except('photo'));
    
        // Vérifier si une nouvelle photo a été uploadée
        if ($request->hasFile('photo')) {
            // Supprimer l'ancienne photo si elle existe
            if ($projet->photo) {
                Storage::delete('public/photos/' . $projet->photo);
            }
            
            // Sauvegarder la nouvelle photo
            $photo = $request->file('photo');
            $photoName = time() . '_' . $photo->getClientOriginalName();
            $photo->storeAs('public/photos', $photoName);
            $projet->photo = $photoName;
        }
    
        $projet->update(); 
        
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
