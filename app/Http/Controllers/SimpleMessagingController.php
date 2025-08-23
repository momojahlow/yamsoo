<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SimpleMessagingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $selectedContactId = $request->query('selectedContactId');
        
        // Récupérer toutes les conversations de l'utilisateur
        $conversations = $user->conversations()
            ->with(['participants', 'messages' => function($query) {
                $query->latest()->limit(1);
            }])
            ->orderBy('last_message_at', 'desc')
            ->get()
            ->map(function ($conv) use ($user) {
                $otherUser = $conv->participants->where('id', '!=', $user->id)->first();
                $lastMessage = $conv->messages->first();
                
                return [
                    'id' => $conv->id,
                    'name' => $otherUser ? $otherUser->name : 'Conversation',
                    'other_user_id' => $otherUser ? $otherUser->id : null,
                    'last_message' => $lastMessage ? $lastMessage->content : null,
                    'last_message_time' => $lastMessage ? $lastMessage->created_at->diffForHumans() : null,
                ];
            });
        
        // Conversation et messages sélectionnés
        $selectedConversation = null;
        $messages = [];
        
        if ($selectedContactId) {
            $targetUser = User::find($selectedContactId);
            
            if ($targetUser) {
                // Chercher ou créer une conversation
                $conversation = $this->findOrCreateConversation($user, $targetUser);
                
                $selectedConversation = [
                    'id' => $conversation->id,
                    'name' => $targetUser->name,
                    'other_user_id' => $targetUser->id,
                ];
                
                // Charger les messages
                $messages = $conversation->messages()
                    ->with('user')
                    ->orderBy('created_at', 'asc')
                    ->get()
                    ->map(function ($msg) {
                        return [
                            'id' => $msg->id,
                            'content' => $msg->content,
                            'user_id' => $msg->user_id,
                            'user_name' => $msg->user->name,
                            'created_at' => $msg->created_at->format('H:i'),
                            'is_mine' => $msg->user_id === auth()->id(),
                        ];
                    })->toArray();
            }
        }
        
        return Inertia::render('SimpleMessaging', [
            'conversations' => $conversations->toArray(),
            'selectedConversation' => $selectedConversation,
            'messages' => $messages,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
            ]
        ]);
    }
    
    public function sendMessage(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'message' => 'required|string|max:1000',
        ]);
        
        $user = $request->user();
        $conversation = Conversation::findOrFail($request->conversation_id);
        
        // Vérifier que l'utilisateur fait partie de la conversation
        if (!$conversation->participants->contains($user)) {
            return redirect()->back()->with('error', 'Accès non autorisé');
        }
        
        // Créer le message
        Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'content' => $request->message,
            'type' => 'text',
        ]);
        
        // Mettre à jour la conversation
        $conversation->update(['last_message_at' => now()]);
        
        // Rediriger vers la même conversation
        $otherUser = $conversation->participants->where('id', '!=', $user->id)->first();
        
        return redirect("/simple-messaging?selectedContactId={$otherUser->id}");
    }
    
    private function findOrCreateConversation(User $user1, User $user2)
    {
        // Chercher une conversation existante
        $conversation = Conversation::where('type', 'private')
            ->whereHas('participants', function ($query) use ($user1) {
                $query->where('user_id', $user1->id);
            })
            ->whereHas('participants', function ($query) use ($user2) {
                $query->where('user_id', $user2->id);
            })
            ->first();
        
        if (!$conversation) {
            // Créer une nouvelle conversation
            DB::transaction(function () use (&$conversation, $user1, $user2) {
                $conversation = Conversation::create([
                    'type' => 'private',
                    'created_by' => $user1->id,
                    'last_message_at' => now(),
                ]);
                
                $conversation->participants()->attach([$user1->id, $user2->id]);
            });
        }
        
        return $conversation;
    }
}
