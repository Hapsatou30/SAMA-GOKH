<?php

namespace App\Traits;

use App\Models\Notification;

trait NotifiableTrait
{
    public function addNotification($userId, $projetId, $contenu)
    {
        Notification::create([
            'user_id' => $userId,
            'projet_id' => $projetId,
            'contenu' => $contenu,
            'statut' => 'non-lue'
        ]);
    }
}
