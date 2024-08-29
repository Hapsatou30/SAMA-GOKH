<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNotificationRequest;
use App\Http\Requests\UpdateNotificationRequest;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{

    public function getAllNotifications()
    {
        // Obtenir l'ID de l'utilisateur connecté
        $userId = Auth::id();
        
        // Récupérer toutes les notifications non lues pour l'utilisateur connecté
        $notifications = Notification::where('user_id', $userId)
                                      ->where('statut', 'non-lue')
                                      ->orderBy('created_at', 'desc') // Trier par date de création, les plus récentes en premier
                                      ->get();
        
        return response()->json([
            'status' => true,
            'data' => $notifications
        ], 200);
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
    
    public function deleteNotification($id)
    {
        $notification = Notification::find($id);

        if ($notification && $notification->user_id == auth()->id()) {
            $notification->delete();

            return response()->json(['message' => 'Notification supprimée'], 200);
        }

        return response()->json(['message' => 'Notification non trouvée ou accès refusé'], 404);
    }
}
