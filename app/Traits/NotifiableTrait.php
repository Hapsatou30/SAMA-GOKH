<?php

namespace App\Traits;

use App\Models\User;
use App\Models\Notification;

trait NotifiableTrait
{
    public function notifyAllUsers($projetId, $contenu)
    {
        // Récupérer tous les utilisateurs
        $users = User::all();

        foreach ($users as $user) {
            // Créer une notification pour chaque utilisateur
            Notification::create([
                'user_id' => $user->id,
                'projet_id' => $projetId,
                'contenu' => $contenu,
                'statut' => 'non-lue'
            ]);
        }
    }
}
