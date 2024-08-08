<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentaireRequest;
use App\Http\Requests\UpdateCommentaireRequest;
use App\Models\Commentaire;

class CommentaireController extends Controller
{
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
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCommentaireRequest $request)
    {
        $commentaire = Commentaire::create($request->validated());
    return response()->json([
        'message' => 'Commentaire ajouté avec succès!',
        'data' => $commentaire
    ], 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(Commentaire $commentaire)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Commentaire $commentaire)
    {
        //
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
