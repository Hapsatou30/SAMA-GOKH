<?php

namespace App\Http\Controllers;

use App\Models\Projet;
use App\Http\Requests\StoreProjetRequest;
use App\Http\Requests\UpdateProjetRequest;
use App\Mail\Email;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class ProjetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projets = Projet::all();
        return response()->json($projets);
    }

    /**
     * Show the form for creating a new resource.
     */
    /**
     * Store a newly created resource in storage.
     */
    // public function store(StoreProjetRequest $request)
    // {
    //     $projet = new Projet();
    //     $projet->fill($request->validated());
    //     $projet->save();
    //     return self::customJsonResponse("Projet créé avec succès", $projet, 201);

    // }
    public function store(StoreProjetRequest $request)
    {
        $projet = new Projet();
        $projet->fill($request->validated());
        $projet->save();
    
        $user = Auth::user();
    
        // Vérifier si l'utilisateur a le rôle 'habitant'
        if ($user && $user->role_id === 3) { // Vérification basée sur role_id
            // Récupérer les informations de l'habitant
            $habitant = $user->habitant;
    
            // Envoyer l'email
            Mail::to($user->email)->send(new Email($habitant, $projet->nom));
        }
    
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
        $projet->fill($request->validated());
        $projet->update();
        return $this->customJsonResponse("Projet modifiée avec succès", $projet);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Projet $projet)
    {
        $projet->delete();
        return $this->customJsonResponse("Projet supprimé avec succès", 204);
    }


    
}
