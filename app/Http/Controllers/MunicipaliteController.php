<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Municipalite;
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
        //voir les informations pour une commune
        return response()->json($municipalite);
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMunicipaliteRequest $request, Municipalite $municipalite)
{
    // Vérifier que l'utilisateur courant a le rôle avec ID 1
    if (auth()->user()->role_id !== 1) {
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

    // Mettre à jour les informations de l'utilisateur
    $municipalite->user->update([
        'email' => $request->email,
        'password' => $request->filled('password') ? bcrypt($request->password) : $municipalite->user->password,
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Les donnéés de la commune sont mise à jour avec succès'
    ]);
}



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Municipalite $municipalite)
    {
        // Vérifier que l'utilisateur courant a le rôle avec ID 1
        if (auth()->user()->role_id !== 1) {
            return response()->json(['error' => 'Vous n\'avez pas l\'autorisation de supprimer cette commune.'], 403);
        }
    
        // Supprimer l'utilisateur associé
        $municipalite->user->delete();
    
        // Supprimer la commune
        $municipalite->delete();
    
        // Retourner une réponse JSON avec un message de succès
        return response()->json([
            'status' => true,
            'message' => 'Commune supprimée avec succès'
        ], 200);
    }
    

    //profil d'une municipalite
    public function profile(Municipalite $municipalite)
    {
        // Charger les informations de l'utilisateur associé
        $municipalite->load('user');

        // Retourner les détails de la municipalité avec les informations de l'utilisateur
        return response()->json([
            'municipalite' => $municipalite,
            'user' => [
                'email' => $municipalite->user->email,
                'password' => $municipalite->user->password, // Le mot de passe sera hashé
            ]
        ]);
    }
}
