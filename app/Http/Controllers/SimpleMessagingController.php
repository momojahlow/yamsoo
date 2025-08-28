<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
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

        // Récupérer les préférences de notification pour la conversation sélectionnée
        $notificationsEnabled = true; // Par défaut
        if ($selectedConversation && isset($selectedConversation['id'])) {
            $participant = DB::table('conversation_participants')
                ->where('conversation_id', $selectedConversation['id'])
                ->where('user_id', $user->id)
                ->first();

            if ($participant) {
                $notificationsEnabled = $participant->notifications_enabled ?? true;
            }
        }

        return Inertia::render('Messaging/Index', [
            'conversations' => $conversations->toArray(),
            'selectedConversation' => $selectedConversation,
            'messages' => $messages,
            'notificationsEnabled' => $notificationsEnabled,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->profile?->avatar_url ?? null
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

        // Envoyer des notifications aux autres participants
        $otherParticipants = $conversation->participants()
            ->where('user_id', '!=', $user->id)
            ->where('conversation_participants.status', 'active')
            ->where('conversation_participants.notifications_enabled', true)
            ->get();

        foreach ($otherParticipants as $participant) {
            $participant->notify(new \App\Notifications\NewMessageNotification($message, $user));
        }

        // Toujours rediriger avec Inertia, mais préserver l'état avec des données
        if ($conversation->type === 'group') {
            // Pour un groupe, rediriger avec selectedGroupId
            return redirect("/messagerie?selectedGroupId={$conversation->id}")
                ->with('success', 'Message envoyé dans le groupe')
                ->with('newMessage', [
                    'id' => $message->id,
                    'content' => $message->content,
                    'user_id' => $message->user_id,
                    'created_at' => $message->created_at,
                    'user' => $user->only(['id', 'name', 'email']),
                ]);
        } else {
            // Pour une conversation privée, rediriger avec selectedContactId
            $otherUser = $conversation->participants->where('id', '!=', $user->id)->first();
            return redirect("/messagerie?selectedContactId={$otherUser->id}")
                ->with('success', 'Message envoyé')
                ->with('newMessage', [
                    'id' => $message->id,
                    'content' => $message->content,
                    'user_id' => $message->user_id,
                    'created_at' => $message->created_at,
                    'user' => $user->only(['id', 'name', 'email']),
                ]);
        }
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

    /**
     * Mettre à jour les préférences de notification pour une conversation
     */
    public function updateNotificationSettings(Request $request, Conversation $conversation)
    {
        $user = Auth::user();

        $request->validate([
            'notifications_enabled' => 'required|boolean'
        ]);

        // Vérifier que l'utilisateur est participant de la conversation
        $participant = $conversation->participants()->where('user_id', $user->id)->first();

        if (!$participant) {
            abort(403, 'Vous n\'êtes pas participant de cette conversation');
        }

        // Mettre à jour les préférences de notification
        $conversation->participants()->updateExistingPivot($user->id, [
            'notifications_enabled' => $request->notifications_enabled
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Préférences de notification mises à jour',
            'notifications_enabled' => $request->notifications_enabled
        ]);
    }

    /**
     * Récupérer les préférences de notification pour une conversation
     */
    public function getNotificationSettings(Conversation $conversation)
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur est participant de la conversation
        $participant = $conversation->participants()->where('user_id', $user->id)->first();

        if (!$participant) {
            abort(403, 'Vous n\'êtes pas participant de cette conversation');
        }

        return response()->json([
            'notifications_enabled' => $participant->pivot->notifications_enabled ?? true
        ]);
    }

    /**
     * API pour récupérer les conversations avec compteurs de messages non lus
     * Optimisée pour le dropdown Messenger
     */
    public function getConversationsSummary()
    {
        $user = Auth::user();

        // Requête optimisée avec jointures pour éviter le problème N+1
        $conversations = DB::table('conversations as c')
            ->join('conversation_participants as cp', 'c.id', '=', 'cp.conversation_id')
            ->leftJoin('messages as m', function ($join) {
                $join->on('c.id', '=', 'm.conversation_id')
                     ->whereRaw('m.id = (SELECT MAX(id) FROM messages WHERE conversation_id = c.id)');
            })
            ->leftJoin('users as msg_user', 'm.user_id', '=', 'msg_user.id')
            ->leftJoin('profiles as msg_profile', 'msg_user.id', '=', 'msg_profile.user_id')
            ->where('cp.user_id', $user->id)
            ->where('cp.status', 'active')
            ->select([
                'c.id',
                'c.name',
                'c.type',
                'c.updated_at',
                'm.content as last_message_content',
                'm.created_at as last_message_time',
                'm.user_id as last_message_user_id',
                'msg_user.name as last_message_user_name'
            ])
            ->orderBy('c.updated_at', 'desc')
            ->limit(20)
            ->get();

        // Traiter les conversations et calculer les messages non lus
        $conversationsSummary = collect();
        $totalUnreadCount = 0;

        foreach ($conversations as $conv) {
            // Calculer le nombre de messages non lus pour cette conversation
            $unreadCount = DB::table('messages')
                ->where('conversation_id', $conv->id)
                ->where('user_id', '!=', $user->id)
                ->whereNotExists(function ($query) use ($user, $conv) {
                    $query->select(DB::raw(1))
                          ->from('message_reads')
                          ->whereColumn('message_reads.message_id', 'messages.id')
                          ->where('message_reads.user_id', $user->id);
                })
                ->count();

            // Récupérer l'autre participant pour les conversations privées
            $otherParticipant = null;
            if ($conv->type === 'private') {
                $otherParticipant = DB::table('users')
                    ->join('conversation_participants', 'users.id', '=', 'conversation_participants.user_id')
                    ->leftJoin('profiles', 'users.id', '=', 'profiles.user_id')
                    ->where('conversation_participants.conversation_id', $conv->id)
                    ->where('users.id', '!=', $user->id)
                    ->select('users.id', 'users.name', 'profiles.avatar_url', 'users.is_online')
                    ->first();
            }

            // Préparer les données du dernier message
            $lastMessage = null;
            if ($conv->last_message_content) {
                $lastMessage = [
                    'content' => $conv->last_message_content,
                    'created_at' => $conv->last_message_time,
                    'user_name' => $conv->last_message_user_name,
                    'is_own' => $conv->last_message_user_id === $user->id
                ];
            }

            $conversationData = [
                'id' => $conv->id,
                'name' => $conv->type === 'private'
                    ? ($otherParticipant ? $otherParticipant->name : 'Conversation privée')
                    : $conv->name,
                'type' => $conv->type,
                'avatar' => $conv->type === 'private'
                    ? ($otherParticipant->avatar_url ?? null)
                    : null,
                'last_message' => $lastMessage,
                'unread_count' => $unreadCount,
                'is_online' => $conv->type === 'private'
                    ? ($otherParticipant->is_online ?? false)
                    : null,
                'participants_count' => $conv->type === 'group'
                    ? DB::table('conversation_participants')->where('conversation_id', $conv->id)->count()
                    : null,
                'other_participant' => $conv->type === 'private' && $otherParticipant
                    ? [
                        'id' => $otherParticipant->id,
                        'name' => $otherParticipant->name,
                        'avatar' => $otherParticipant->avatar_url ?? null
                    ]
                    : null
            ];

            $conversationsSummary->push($conversationData);
            $totalUnreadCount += $unreadCount;
        }

        return response()->json([
            'conversations' => $conversationsSummary->values(), // Réindexer la collection
            'total_unread_count' => $totalUnreadCount,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->profile?->avatar_url ?? null
            ]
        ]);
    }
}
