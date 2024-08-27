<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Municipalite;
use App\Mail\MunicipaliteCreated;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\StoreMunicipaliteRequest;
use App\Http\Requests\UpdateMunicipaliteRequest;

class MunicipaliteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //afficher les listes des communes
        $municipalites = Municipalite::all();
        return response()->json($municipalites);
    }

    public function getCommunesByRegion($region)
{
    // Récupérer toutes les communes de la région spécifiée
    $municipalites = Municipalite::where('region', $region)->get();

    // Vérifier s'il y a des résultats
    if ($municipalites->isEmpty()) {
        return response()->json(['message' => 'Aucune commune trouvée pour cette région.'], 404);
    }

    // Retourner la liste des communes
    return response()->json($municipalites);
}


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMunicipaliteRequest $request)
    {
        // Vérifier que l'utilisateur courant a le rôle avec ID 1
        if (auth()->user()->role_id !== 1) {
            return response()->json(['error' => 'Vous n\'avez pas l\'autorisation d\'ajouter une commune.'], 403);
        }
    
        // Validation des données de la commune
        $validator = validator(
            $request->all(),
            [
                'nom_commune' => ['required', 'string', 'max:255', 'unique:municipalites'],
                'departement' => ['required', 'string'],
                'region' => ['required', 'string'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8'],
            ]
        );
        
        // Si les données ne sont pas valides, renvoyer les erreurs
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
    
        // Créer un nouvel utilisateur
        $user = User::create([
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role_id' => 2,
        ]);
    
        // Créer une nouvelle commune
        $municipalite = new Municipalite();
        $municipalite->user_id = $user->id;
        $municipalite->nom_commune = $request->nom_commune;
        $municipalite->departement = $request->departement;
        $municipalite->region = $request->region;
        $municipalite->save();

         // Envoyer un email à la municipalité
         Mail::to($request->email)->send(new MunicipaliteCreated($municipalite, $request->password));

    
        return response()->json([
            'status' => true,
            'message' => 'Commune créée avec succès',
            'data' => $municipalite
        ]);
    }
    


    /**
     * Display the specified resource.
     */
    public function show(Municipalite $municipalite)
    {
        return response()->json([
            'nom_commune' => $municipalite->nom_commune,
            'email' => $municipalite->user->email,  // Assurez-vous que la relation est correcte
            'departement' => $municipalite->departement,
            'region' => $municipalite->region,
        ]);
    }
    


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMunicipaliteRequest $request, Municipalite $municipalite)
    {
        $currentUser = auth()->user();
        
        // Vérifier que l'utilisateur courant est soit un admin, soit la municipalité concernée
        if ($currentUser->role_id !== 1 && $currentUser->id !== $municipalite->user_id) {
            return response()->json(['error' => 'Vous n\'avez pas l\'autorisation de mettre à jour cette commune.'], 403);
        }
    
        // Validation des données
        $validator = validator(
            $request->all(),
            [
                'nom_commune' => ['required', 'string', 'max:255', 'unique:municipalites,nom_commune,' . $municipalite->id],
                'departement' => ['required', 'string'],
                'region' => ['required', 'string'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $municipalite->user_id],
                'password' => ['sometimes', 'nullable', 'string', 'min:8'],
            ]
        );
    
        // Si les données ne sont pas valides, renvoyer les erreurs
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
    
        // Mettre à jour les informations de la municipalité
        $municipalite->update($request->only('nom_commune', 'departement', 'region'));
    
        // Mettre à jour les informations de l'utilisateur uniquement si l'utilisateur courant est la municipalité concernée ou si c'est un admin
        $user = $municipalite->user;
        if ($currentUser->id === $user->id || $currentUser->role_id === 1) {
            $user->update([
                'email' => $request->email,
                'password' => $request->filled('password') ? bcrypt($request->password) : $user->password,
            ]);
        }
    
        return response()->json([
            'status' => true,
            'message' => 'Les données de la commune ont été mises à jour avec succès'
        ]);
    }
    


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Municipalite $municipalite)
    {
        // Vérifiez les permissions de l'utilisateur
        if (auth()->user()->role_id !== 1) {
            return response()->json(['error' => 'Vous n\'avez pas l\'autorisation de supprimer cette commune.'], 403);
        }
    
        try {
            // Supprimer l'utilisateur associé si nécessaire
            if ($municipalite->user) {
                $municipalite->user->delete();
            }
    
            // Supprimer la commune
            $municipalite->delete();
    
            return response()->json([
                'status' => true,
                'message' => 'Commune supprimée avec succès'
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Une erreur est survenue lors de la suppression.'], 500);
        }
    }
    

     /**
     * Récupérer les informations de la municipalité connectée.
     */
    public function getMunicipaliteConnectee()
    {
        // Récupérer l'utilisateur connecté
        $user = auth()->user();
    
        // Vérifier que l'utilisateur est bien associé à une municipalité
        $municipalite = Municipalite::where('user_id', $user->id)->first();
    
        if (!$municipalite) {
            return response()->json(['error' => 'Aucune municipalité associée à cet utilisateur.'], 404);
        }
    
        // Retourner les informations de la municipalité avec l'ID inclus
        return response()->json([
            'id' => $municipalite->id, // Ajout de l'ID
            'nom_commune' => $municipalite->nom_commune,
            'email' => $user->email,
            'departement' => $municipalite->departement,
            'region' => $municipalite->region,
        ]);
    }
    

 
public function getHabitantsConnecte()
{
    // Récupérer l'utilisateur connecté
    $user = auth()->user();
    
    // Récupérer la municipalité associée à l'utilisateur
    $municipalite = Municipalite::where('user_id', $user->id)->first();
    
    if (!$municipalite) {
        return response()->json(['error' => 'Aucune municipalité associée à cet utilisateur.'], 404);
    }
    
    // Inclure les informations de l'utilisateur avec les habitants
    $habitants = $municipalite->habitants()->with('user')->get();

    return response()->json($habitants);
}

}
