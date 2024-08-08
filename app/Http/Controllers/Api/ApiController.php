<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ApiController extends Controller
{
    //inscription
    public function register(Request $request)
    {
        // Validation des données
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role_id' => ['required', 'exists:roles,id'], // Valider que le rôle existe
        ]);
    
        // Enregistrement des données
        $user = User::create([
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role_id' => $request->role_id, // Assigner le rôle à l'utilisateur
        ]);
    
        // Envoi du token d'authentification
        $token = $user->createToken('authToken')->plainTextToken;
    
        return response()->json([
            "status" => true,
            "message" => "Utilisateur enregistré avec succès",
            "token" => $token,
        ]);
    }
    
}
