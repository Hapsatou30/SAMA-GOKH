<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHabitantRequest;
use App\Http\Requests\UpdateHabitantRequest;
use App\Models\Habitant;

class HabitantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //methode pour recuperer la liste des habitants
        $habitants = Habitant::all();
        return response()->json([
            'status' => true,
            'message' => 'la liste des habitants',
            'data' => $habitants
        ]);
    }

    

    public function habitantsByCommune($municipaliteId)
    {
        // Récupérer les habitants en fonction de l'identifiant de la municipalité
        $habitants = Habitant::where('municipalite_id', $municipaliteId)->get();
    
        return response()->json([
            'status' => true,
            'message' => 'La liste des habitants de la commune',
            'data' => $habitants
        ]);
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
    public function store(StoreHabitantRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Habitant $habitant)
    {
        try {
            // Charger l'utilisateur associé pour récupérer l'email
            $habitant->load('user');
    
            return response()->json([
                'status' => true,
                'message' => 'Détails de l\'habitant récupérés avec succès.',
                'data' => $habitant
            ], 200);
        } catch (\Exception $e) {
            // Gérer les erreurs potentielles
            return response()->json([
                'status' => false,
                'message' => 'Erreur lors de la récupération des détails de l\'habitant.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Habitant $habitant)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateHabitantRequest $request, Habitant $habitant)
    {
           // Récupérer l'utilisateur connecté
    $user = auth()->user();

    // Récupérer l'habitant associé à cet utilisateur
    $habitant = Habitant::where('user_id', $user->id)->first();

    // Vérifier si l'habitant existe
    if (!$habitant) {
        return response()->json([
            "status" => false,
            "message" => "Habitant non trouvé"
        ], 404);
    }

    // Validation des données de la requête
    $validator = validator(
        $request->all(),
        [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8'],
            'nom' => ['required', 'string'],
            'prenom' => ['required', 'string'],
            'telephone' => ['required', 'string', 'unique:habitants,telephone,' . $habitant->id],
            'adresse' => ['required', 'string'],
            'sexe' => ['required', 'string'],
            'date_naiss' => ['required', 'date'],
            'photo' => ['nullable', 'string'],
            'profession' => ['required', 'string'],
            'numero_identite' => ['required', 'string'],
        ]
    );

    // Si les données ne sont pas valides, renvoyer les erreurs
    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 422);
    }

    // Mettre à jour les informations de l'utilisateur
    $user->email = $request->email;

    // Si un nouveau mot de passe est fourni, le mettre à jour
    if ($request->filled('password')) {
        $user->password = bcrypt($request->password);
    }

    $user->save();

    // Mettre à jour les informations de l'habitant
    $habitant->update([
        'nom' => $request->nom,
        'prenom' => $request->prenom,
        'telephone' => $request->telephone,
        'adresse' => $request->adresse,
        'sexe' => $request->sexe,
        'date_naiss' => $request->date_naiss,
        'photo' => $request->photo,
        'profession' => $request->profession,
        'numero_identite' => $request->numero_identite,
    ]);

    return response()->json([
        "status" => true,
        "message" => "Profil mis à jour avec succès",
        "data" => [
            "user" => $user,
            "habitant" => $habitant
        ]
    ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Habitant $habitant)
    {
        //
    }
}
