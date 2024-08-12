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
        return response()->json($projets);
    }

    /**
     * Show the form for creating a new resource.
     */
    
    public function store(StoreProjetRequest $request)
    {
        $projet = new Projet();
        $projet->fill($request->validated());
        $projet->save();
    
        $user = Auth::user();
    
        if ($user && $user->role_id === 3) { // Vérification basée sur role_id
            $habitant = $user->habitant;
            Mail::to($user->email)->send(new Email($habitant, $projet->nom));
        }
    
        // Ajouter une notification pour chaque utilisateur
        $users = User::all(); // Récupérer tous les utilisateurs
        foreach ($users as $user) {
            $this->addNotification($user->id, $projet->id, "Un nouveau projet a été créé : " . $projet->nom);
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
        $users = User::all(); // Récupérer tous les utilisateurs
        foreach ($users as $user) {
            $this->addNotification($user->id, $projet->id, "Un projet a été supprimé : " . $projet->nom);
        }
    
        $projet->delete();
    
        return response()->json(["message" => "Projet supprimé avec succès"], 204);
    }
    
    

    
}
