<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Habitant;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ApiController extends Controller
{
    //inscription
    // public function register(Request $request)
    // {
    //     // Validation des données
    //     $request->validate([
    //         'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
    //         'password' => ['required', 'string', 'min:8'],
    //         'role_id' => ['required', 'exists:roles,id'], // Valider que le rôle existe
    //     ]);

    //     // Enregistrement des données
    //     $user = User::create([
    //         'email' => $request->email,
    //         'password' => bcrypt($request->password),
    //         'role_id' => $request->role_id, // Assigner le rôle à l'utilisateur
    //     ]);

    //     // Envoi du token d'authentification
    //     $token = $user->createToken('authToken')->plainTextToken;

    //     return response()->json([
    //         "status" => true,
    //         "message" => "Utilisateur enregistré avec succès",
    //         "token" => $token,
    //     ]);
    // }
    public function register(Request $request)
    {
        // Validation des données
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'nom' => ['required', 'string'],
            'prenom' => ['required', 'string'],
            'telephone' => ['required', 'string', 'unique:habitants'],
            'adresse' => ['required', 'string'],
            'sexe' => ['required', 'string'],
            'date_naiss' => ['required', 'date'],
            'photo' => ['nullable', 'string'],
            'profession' => ['required', 'string'],
            'numero_identite' => ['required', 'string'],
        ]);
    
        // Création de l'utilisateur
        $user = User::create([
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role_id' => 3, // Assigner le rôle d'habitant
        ]);
    
        // Création de l'habitant
        $habitant = Habitant::create([
            'user_id' => $user->id,
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
            "message" => "Utilisateur enregistré avec succès"
        ]);
    }
    
    // connexion
    public function login(Request $request)
    {
        // Validation des données
        $validator = validator(
            $request->all(),
            [
                'email' => 'required|email|string',
                'password' => 'required|string',
            ]
        );
        // Si les données ne sont pas valides, renvoyer les erreurs
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        // Si les données sont valides, authentifier l'utilisateur
        $credentials = $request->only('email', 'password');
        $token = auth()->attempt($credentials);
        // Si les informations de connexion ne sont pas correctes, renvoyer une erreur 401  
        if (!$token) {
            return response()->json(['message' => 'Information de connexion incorrectes'], 401);
        }
        // Renvoyer le token d'authentification
        return response()->json([
            "access_token" => $token,
            "token_type" => "bearer",
            "user" => auth()->user(),
            "expires_in" => env("JwT_TTL") * 30  . 'seconds'
        ]);
    }

    // déconnexion
    public function logout(Request $request){
        auth()->logout();
        return response()->json([
            'status' =>true,
           'message' => 'Utilisateur déconnecté'
        ]);
    }

    // récupérer le profil utilisateur connecté
    public function profile(){

        //$userData = auth()->user();
        $userData = request()->user();

        return response()->json([
            "status" => true,
            "message" => "Données de profil",
            "data" => $userData,
            "user_id" => request()->user()->id,
            "email" => request()->user()->email
        ]);
    }

    // refresher le token d'authentification
    public function refreshToken(Request $request){
        $token = auth()->refresh();
        return response()->json([
            "access_token" => $token,
            "token_type" => "bearer",
            "expires_in" => env("JWT_TTL") * 30  .'seconds'
        ]);
    }
}
