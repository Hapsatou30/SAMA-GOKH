<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Habitant;
use Illuminate\Support\Facades\Http;
use App\Models\Municipalite;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ApiController extends Controller
{
   
    public function register(Request $request)
    {
        // Validation des données
        $validator = validator(
            $request->all(),
            [
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
                'image_cni' => ['required', 'file', 'image', 'max:2048'],
                'municipalite_id' => ['required', 'integer', 'exists:municipalites,id'],
            ]
        );

        // Si les données ne sont pas valides, renvoyer les erreurs
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $image_cni = $request->file('image_cni');

        // Vérifier s'il y a un fichier pour l'image CNI
        if ($image_cni) {
            // Chemin temporaire du fichier
            $imagePath = $image_cni->getRealPath();
            $apiKey = env('OCR_SPACE_API_KEY');
            $url = 'https://api.ocr.space/parse/image';

            // Effectuer la requête à l'API OCR Space
            $response = Http::attach('file', file_get_contents($imagePath), $image_cni->getClientOriginalName())
                            ->post($url, [
                                'apikey' => $apiKey,
                                'language' => 'eng',
                            ]);

            $data = $response->json();

            // Vérifier la présence de texte dans la réponse
            if (isset($data['ParsedResults'][0]['ParsedText'])) {
                $extractedText = trim($data['ParsedResults'][0]['ParsedText']);
                
                // Comparer le texte extrait avec le numéro d'identité
                if (strcasecmp($extractedText, $request->numero_identite) !== 0) {
                    return response()->json(['error' => 'Le numéro d\'identité extrait ne correspond pas.'], 422);
                }
            } else {
                return response()->json(['error' => 'Erreur de traitement de l\'image.'], 500);
            }
        }

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
            'image_cni' => $request->image_cni ? $request->file('image_cni')->store('images') : null,
            'municipalite_id' => $request->municipalite_id,
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
    public function profile()
    {
        // Récupérer l'utilisateur connecté
        $user = auth()->user();
    
        // Récupérer les informations de l'habitant associé à cet utilisateur
        $habitant = Habitant::where('user_id', $user->id)->first();
    
        // Vérifier si l'habitant existe
        if (!$habitant) {
            return response()->json([
                "status" => false,
                "message" => "Habitant non trouvé"
            ], 404);
        }
    
        // Retourner les informations de l'utilisateur et de l'habitant
        return response()->json([
            "status" => true,
            "message" => "Données de profil récupérées avec succès",
            "data" => [
                "email" => $user->email,
                "password" => $user->password, 
                "nom" => $habitant->nom,
                "prenom" => $habitant->prenom,
                "telephone" => $habitant->telephone,
                "adresse" => $habitant->adresse,
                "sexe" => $habitant->sexe,
                "date_naiss" => $habitant->date_naiss,
                "photo" => $habitant->photo,
                "profession" => $habitant->profession,
                "numero_identite" => $habitant->numero_identite,
                "municipalite_id" => $habitant->municipalite_id,
            ]
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
