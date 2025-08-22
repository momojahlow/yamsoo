<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;

class DebugMessagingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $selectedContactId = $request->query('selectedContactId');
        
        $debug = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name
            ],
            'selectedContactId' => $selectedContactId,
            'url' => $request->fullUrl(),
            'query_params' => $request->query(),
        ];
        
        // Récupérer les conversations de l'utilisateur
        $conversations = $user->conversations()
            ->with([
                'lastMessage.user',
                'participants' => function ($query) use ($user) {
                    $query->where('user_id', '!=', $user->id);
                }
            ])
            ->orderBy('last_message_at', 'desc')
            ->get();
            
        $debug['conversations_raw'] = $conversations->map(function ($conv) {
            return [
                'id' => $conv->id,
                'name' => $conv->name,
                'type' => $conv->type,
                'participants' => $conv->participants->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'name' => $p->name
                    ];
                })
            ];
        });
        
        // Traitement des conversations
        $conversationsFormatted = $conversations->map(function ($conversation) use ($user) {
            $otherParticipant = $conversation->participants->first();
            
            return [
                'id' => $conversation->id,
                'name' => $conversation->type === 'private' 
                    ? ($otherParticipant ? $otherParticipant->name : 'Utilisateur inconnu')
                    : $conversation->name,
                'type' => $conversation->type,
                'avatar' => $conversation->type === 'private' 
                    ? ($otherParticipant?->profile?->avatar_url ?? null)
                    : null,
                'last_message' => $conversation->lastMessage ? [
                    'content' => $conversation->lastMessage->content,
                    'created_at' => $conversation->lastMessage->created_at,
                    'user_name' => $conversation->lastMessage->user->name
                ] : null,
                'unread_count' => 0,
                'is_online' => $otherParticipant?->isOnline() ?? false,
                'participants_count' => $conversation->participants->count(),
                'other_participant_id' => $otherParticipant?->id,
                'is_new' => false
            ];
        });
        
        $debug['conversations_formatted'] = $conversationsFormatted;
        
        // Logique de sélection
        $selectedConversation = null;
        $targetUser = null;
        
        if ($selectedContactId) {
            $targetUser = User::find($selectedContactId);
            $debug['targetUser'] = $targetUser ? [
                'id' => $targetUser->id,
                'name' => $targetUser->name
            ] : null;
            
            if ($targetUser) {
                // Chercher une conversation existante
                $existingConversation = $conversationsFormatted->first(function ($conv) use ($selectedContactId) {
                    return $conv['type'] === 'private' && $conv['other_participant_id'] == $selectedContactId;
                });
                
                $debug['existing_conversation'] = $existingConversation;
                
                if ($existingConversation) {
                    $selectedConversation = $existingConversation;
                } else {
                    $debug['creating_new_conversation'] = true;
                    // Logique de création automatique ici...
                }
            }
        }
        
        $debug['selectedConversation'] = $selectedConversation;
        
        return response()->json($debug, 200, [], JSON_PRETTY_PRINT);
    }
}
