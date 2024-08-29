<?php

namespace App\Http\Controllers;

use App\Mail\Email;
use App\Models\User;
use App\Models\Projet;
use App\Models\Habitant;
use App\Models\Municipalite;
use Illuminate\Http\Request;
use App\Mail\ProjetEtatChange;
use App\Traits\NotifiableTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreProjetRequest;
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
            // soit à la municipalité elle-même, et dont l'état est différent de "rejeté"
            $projets = Projet::where(function($query) use ($habitantUserIds, $municipalite) {
                $query->whereIn('user_id', $habitantUserIds) // Projets créés par les habitants
                      ->orWhere('user_id', $municipalite->user_id) // Projets créés par la municipalité
                      ->where('etat', '!=', 'rejeté'); // Filtrer pour exclure les projets avec état "rejeté"
            })
            ->where('etat', 'approuvé') // Filtrer pour inclure uniquement les projets avec état "approuvé"
            ->get();
    
            // Vérifier si des projets ont été trouvés
            if ($projets->isEmpty()) {
                // Retourner une réponse 404 si aucun projet n'est trouvé
                return response()->json(['message' => 'Aucun projet approuvé trouvé pour cette municipalité'], 404);
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

            // Récupérer les projets associés à la municipalité de l'habitant avec le statut "approuvé"
    $projets = Projet::where('municipalite_id', $habitant->municipalite_id)
    ->where('statut', 'approuvé') 
    ->get();

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

    // Enregistrer les modifications
    $projet->save();  // Utilisez save() pour persister les changements

    return $this->customJsonResponse("Projet modifié avec succès", $projet);
}


public function updateEtat(Request $request, $id)
{
    // Récupérer l'utilisateur connecté
    $user = Auth::user();

    // Vérifier si l'utilisateur est associé à une municipalité
    $municipalite = $user->municipalites;
    if (!$municipalite) {
        return response()->json(['error' => 'Vous n\'êtes pas autorisé à modifier l\'état de ce projet.'], 403);
    }

    // Récupérer le projet
    $projet = Projet::findOrFail($id);

    // Récupérer l'utilisateur associé au projet
    $utilisateur = $projet->user;

    // Vérifier si l'utilisateur associé au projet existe
    if (!$utilisateur) {
        return response()->json(['error' => 'Aucun utilisateur associé à ce projet.'], 404);
    }

    // Récupérer l'habitant associé à l'utilisateur
    $habitant = $utilisateur->habitant;

    // Vérifier si l'habitant associé à l'utilisateur existe
    if (!$habitant) {
        return response()->json(['error' => 'Aucun habitant associé à cet utilisateur.'], 404);
    }

    // Mettre à jour l'état du projet
    $projet->etat = $request->input('etat');
    $projet->save();

    try {
        // Envoyer un email à l'habitant associé au projet
        Mail::to($utilisateur->email)->send(new ProjetEtatChange($habitant, $projet->nom));
    } catch (\Exception $e) {
        Log::error('Erreur lors de l\'envoi de l\'email: ' . $e->getMessage());
        return response()->json(['error' => 'Erreur lors de l\'envoi de l\'email.'], 500);
    }

    return response()->json(['message' => 'État du projet mis à jour avec succès.', 'data' => $projet]);
}
    

    
    public function getProjetsByHabitant($habitantId)
{
    try {
        // Récupérer l'habitant en fonction de l'ID fourni
        $habitant = Habitant::findOrFail($habitantId);

        // Récupérer tous les projets associés à cet habitant
        $projets = Projet::where('user_id', $habitant->user_id)->get();

        // Vérifier si des projets ont été trouvés
        if ($projets->isEmpty()) {
            // Retourner une réponse 404 si aucun projet n'est trouvé
            return response()->json(['message' => 'Aucun projet trouvé pour cet habitant'], 404);
        }

        // Retourner les projets trouvés avec une réponse 200
        return response()->json(['data' => $projets], 200);
    } catch (\Exception $e) {
        // En cas d'erreur, loguer l'erreur pour débogage
        Log::error('Erreur lors de la récupération des projets par habitant: ' . $e->getMessage());

        // Retourner une réponse 500 avec un message générique
        return response()->json(['error' => 'Erreur serveur, veuillez réessayer plus tard.'], 500);
    }
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
