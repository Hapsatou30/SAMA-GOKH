<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNotificationRequest;
use App\Http\Requests\UpdateNotificationRequest;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{

    public function getUserNotifications()
    {
        $notifications = Notification::where('user_id', auth()->id())
                                      ->where('statut', 'non-lue')
                                      ->get();

        return response()->json($notifications, 200);
    }
    protected function addNotification($userId, $projetId, $contenu)
    {
        Notification::create([
            'user_id' => $userId,
            'projet_id' => $projetId,
            'contenu' => $contenu,
            'statut' => 'non-lue'
        ]);
    }

    public function markAsRead($id)
    {
        $notification = Notification::find($id);
    
        if ($notification && $notification->user_id == auth()->id()) {
            $notification->statut = 'lue';
            $notification->save();
    
            return response()->json(['message' => 'Notification marquée comme lue'], 200);
        }
    
        return response()->json(['message' => 'Notification non trouvée ou accès refusé'], 404);
    }
    

}
