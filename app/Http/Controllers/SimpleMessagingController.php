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
                'other_participant_id' => $otherUser ? $otherUser->id : null,
                'last_message' => $lastMessage ? [
                    'content' => $lastMessage->content,
                    'created_at' => $lastMessage->created_at ? $lastMessage->created_at->diffForHumans() : '',
                    'user_name' => $lastMessage->user->name,
                    'is_own' => $lastMessage->user_id === $user->id
                ] : null,
                'unread_count' => 0,
                'is_online' => $otherUser?->isOnline() ?? false,
                'participants_count' => $conversation->participants()->count(),
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
                    'other_participant_id' => $targetUser->id,
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
                    'other_participant_id' => null,
                    'participants_count' => $conversation->participants()->count(),
                ];

                $messages = $this->loadMessages($conversation, $user);
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

    private function loadMessages(Conversation $conversation, User $user): array
    {
        return $conversation->messages()
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) use ($user) {
                return [
                    'id' => $msg->id,
                    'content' => $msg->content,
                    'user_id' => $msg->user_id,
                    'user_name' => $msg->user->name,
                    'created_at' => $msg->created_at ? $msg->created_at->format('H:i') : '',
                    'is_mine' => $msg->user_id === $user->id,
                ];
            })->toArray();
    }
}
