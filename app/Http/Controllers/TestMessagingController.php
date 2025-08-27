<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use Inertia\Inertia;

class TestMessagingController extends Controller
{
    public function index()
    {
        $users = User::take(3)->get();
        $conversations = Conversation::with(['messages.user', 'participants'])->get();
        $messages = Message::with('user')->orderBy('created_at', 'desc')->take(10)->get();

        // Vérifier si nous avons des données
        if ($users->isEmpty()) {
            return Inertia::render('TestMessaging', [
                'users' => [],
                'conversations' => [],
                'recent_messages' => [],
                'error' => 'Aucun utilisateur trouvé. Exécutez les seeders d\'abord.'
            ]);
        }
        
        return Inertia::render('TestMessaging', [
            'users' => $users,
            'conversations' => $conversations->map(function ($conv) {
                return [
                    'id' => $conv->id,
                    'name' => $conv->name,
                    'type' => $conv->type,
                    'created_by' => $conv->created_by,
                    'participants' => $conv->participants->map(function ($participant) {
                        return [
                            'id' => $participant->id,
                            'name' => $participant->name,
                        ];
                    }),
                    'messages_count' => $conv->messages->count(),
                    'last_message' => $conv->messages->last() ? [
                        'content' => $conv->messages->last()->content,
                        'user' => $conv->messages->last()->user->name,
                        'created_at' => $conv->messages->last()->created_at,
                    ] : null
                ];
            }),
            'recent_messages' => $messages->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'content' => $msg->content,
                    'conversation_id' => $msg->conversation_id,
                    'user' => $msg->user->name,
                    'created_at' => $msg->created_at,
                ];
            })
        ]);
    }
    
    public function sendTest(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'message' => 'required|string|max:500'
        ]);
        
        $user = $request->user();
        $conversation = Conversation::findOrFail($request->conversation_id);
        
        // Vérifier que l'utilisateur fait partie de la conversation
        if (!$conversation->hasParticipant($user)) {
            return redirect()->back()->with('error', 'Accès non autorisé');
        }
        
        // Créer le message
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'content' => $request->message,
            'type' => 'text',
        ]);
        
        $conversation->update(['last_message_at' => now()]);
        
        return redirect()->back()->with('success', 'Message envoyé ! ID: ' . $message->id);
    }
}
