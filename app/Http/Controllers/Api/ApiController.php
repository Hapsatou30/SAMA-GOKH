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
    
    // connexion
    public function login(Request $request)
    {
       $validator = validator(
         $request->all(),
         [
           'email' =>'required|email|string',
           'password' => 'required|string',
         ]
         );
         if ($validator->fails()) {
           return response()->json(['error' => $validator->errors()], 422);
         }
         $credentials = $request->only('email', 'password');
            $token = auth()->attempt($credentials);
            if (!$token) {
               return response()->json(['message' => 'Information de connexion incorrectes'], 401);
            }
            return response()->json([
                "access_token" => $token,
                "token_type" => "bearer",
                "user" => auth()->user(), 
                "expires_in" => env ("JwT_TTL") * 30  . 'seconds'
            ]);
}
}
