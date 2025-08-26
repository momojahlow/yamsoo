<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Events\MessageSent;

class SimpleMessagingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $selectedContactId = $request->query('selectedContactId');
        $selectedGroupId = $request->query('selectedGroupId');

        // Récupérer toutes les conversations de l'utilisateur via la table pivot
        $userConversations = DB::table('conversation_participants')
            ->join('conversations', 'conversation_participants.conversation_id', '=', 'conversations.id')
            ->where('conversation_participants.user_id', $user->id)
            ->whereNull('conversation_participants.left_at')
            ->select('conversations.*')
            ->orderBy('conversations.last_message_at', 'desc')
            ->get();

        $conversations = collect();

        foreach ($userConversations as $conv) {
            $conversation = Conversation::find($conv->id);
            if (!$conversation) continue;

            $otherUser = null;
            if ($conversation->type === 'private') {
                $otherUser = $conversation->participants()->where('user_id', '!=', $user->id)->first();
            }

            $lastMessage = $conversation->messages()->latest()->first();

            $conversations->push([
                'id' => $conversation->id,
                'name' => $conversation->type === 'group' ? $conversation->name : ($otherUser ? $otherUser->name : 'Conversation'),
                'type' => $conversation->type,
                'avatar' => $conversation->type === 'private' ? ($otherUser?->profile?->avatar_url ?? null) : null,
                'other_participant_id' => $otherUser ? $otherUser->id : null,
                'last_message' => $lastMessage ? [
                    'content' => $lastMessage->content,
                    'created_at' => $lastMessage->created_at ? $lastMessage->created_at->toISOString() : '',
                    'user_name' => $lastMessage->user->name,
                    'is_own' => $lastMessage->user_id === $user->id
                ] : null,
                'unread_count' => 0,
                'is_online' => $otherUser?->isOnline() ?? false,
                'participants_count' => $conversation->participants()->count(),
                'is_new' => false
            ]);
        }

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
                    'type' => 'private',
                    'avatar' => $targetUser->profile?->avatar_url ?? null,
                    'other_participant_id' => $targetUser->id,
                    'last_message' => null,
                    'unread_count' => 0,
                    'is_online' => $targetUser->isOnline(),
                    'participants_count' => 1,
                    'is_new' => false
                ];

                $messages = $this->loadMessages($conversation, $user);
            }
        } elseif ($selectedGroupId) {
            // Conversation de groupe
            $conversation = Conversation::find($selectedGroupId);
            if ($conversation && $conversation->hasParticipant($user)) {
                $selectedConversation = [
                    'id' => $conversation->id,
                    'name' => $conversation->name,
                    'type' => 'group',
                    'avatar' => null,
                    'other_participant_id' => null,
                    'last_message' => null,
                    'unread_count' => 0,
                    'is_online' => false,
                    'participants_count' => $conversation->participants()->count(),
                    'is_new' => false
                ];

                $messages = $this->loadMessages($conversation, $user);
            }
        }

        return Inertia::render('Messaging/Index', [
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
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'content' => $request->message,
            'type' => 'text',
        ]);

        // Charger les relations nécessaires pour l'événement
        $message->load('user.profile');

        // Mettre à jour la conversation
        $conversation->update(['last_message_at' => now()]);

        // Déclencher l'événement pour le temps réel
        Log::info('🚀 Déclenchement événement MessageSent', [
            'message_id' => $message->id,
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'content' => $message->content
        ]);

        broadcast(new MessageSent($message, $user));

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

    /**
     * API pour récupérer les nouveaux messages (polling fallback)
     */
    public function getMessagesSince(Request $request, Conversation $conversation, $messageId = 0)
    {
        $user = $request->user();

        // Vérifier que l'utilisateur fait partie de la conversation
        if (!$conversation->participants->contains($user)) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        // Récupérer les messages plus récents que $messageId
        $messages = $conversation->messages()
            ->with('user.profile')
            ->where('id', '>', $messageId)
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')  // Ordre secondaire par ID
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'content' => $msg->content,
                    'type' => $msg->type ?? 'text',
                    'file_url' => $msg->file_url,
                    'file_name' => $msg->file_name,
                    'file_size' => $msg->formatted_file_size ?? null,
                    'created_at' => $msg->created_at ? $msg->created_at->toISOString() : '',
                    'is_edited' => false,
                    'edited_at' => null,
                    'user' => [
                        'id' => $msg->user->id,
                        'name' => $msg->user->name,
                        'avatar' => $msg->user->profile?->avatar_url ?? null
                    ],
                    'reply_to' => null,
                    'reactions' => []
                ];
            });

        return response()->json(['messages' => $messages]);
    }

    /**
     * Page de test pour le chat temps réel
     */
    public function testRealtimeChat(Request $request)
    {
        $user = $request->user();

        // Récupérer les utilisateurs de test
        $user1 = User::where('email', 'user1@test.com')->first();
        $user2 = User::where('email', 'user2@test.com')->first();

        if (!$user1 || !$user2) {
            return redirect()->back()->with('error', 'Utilisateurs de test non trouvés. Exécutez: php artisan test:realtime-chat');
        }

        // Déterminer l'autre utilisateur
        $otherUser = $user->id === $user1->id ? $user2 : $user1;

        // Trouver la conversation
        $conversation = Conversation::where('type', 'private')
            ->whereHas('participants', function ($query) use ($user1) {
                $query->where('user_id', $user1->id);
            })
            ->whereHas('participants', function ($query) use ($user2) {
                $query->where('user_id', $user2->id);
            })
            ->first();

        if (!$conversation) {
            return redirect()->back()->with('error', 'Conversation de test non trouvée. Exécutez: php artisan test:realtime-chat');
        }

        // Charger les messages
        $messages = $this->loadMessages($conversation, $user);

        return Inertia::render('TestRealtimeChat', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'otherUser' => [
                'id' => $otherUser->id,
                'name' => $otherUser->name,
                'email' => $otherUser->email,
            ],
            'conversation' => [
                'id' => $conversation->id,
                'type' => $conversation->type,
            ],
            'messages' => $messages,
        ]);
    }

    private function loadMessages(Conversation $conversation, User $user): array
    {
        return $conversation->messages()
            ->with('user.profile')
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')  // Ordre secondaire par ID pour éviter les conflits
            ->get()
            ->map(function ($msg) use ($user) {
                return [
                    'id' => $msg->id,
                    'content' => $msg->content,
                    'type' => $msg->type ?? 'text',
                    'file_url' => $msg->file_url,
                    'file_name' => $msg->file_name,
                    'file_size' => $msg->formatted_file_size ?? null,
                    'created_at' => $msg->created_at ? $msg->created_at->toISOString() : '',
                    'is_edited' => false,
                    'edited_at' => null,
                    'user' => [
                        'id' => $msg->user->id,
                        'name' => $msg->user->name,
                        'avatar' => $msg->user->profile?->avatar_url ?? null
                    ],
                    'reply_to' => null,
                    'reactions' => []
                ];
            })->toArray();
    }
}
