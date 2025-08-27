<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\ConversationActivity;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ConversationController extends Controller
{
    /**
     * Obtenir toutes les conversations de l'utilisateur
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $conversations = $user->conversations()
            ->with([
                'participants' => function($query) use ($user) {
                    $query->where('user_id', '!=', $user->id)
                          ->whereNull('conversation_participants.left_at');
                },
                'lastMessage.user',
                'creator'
            ])
            ->whereNull('conversation_participants.left_at')
            ->orderBy('last_message_at', 'desc')
            ->get()
            ->map(function ($conversation) use ($user) {
                return [
                    'id' => $conversation->id,
                    'type' => $conversation->type,
                    'name' => $conversation->isGroup() ? $conversation->name : $conversation->participants->first()?->name,
                    'description' => $conversation->description,
                    'avatar' => $conversation->isGroup() ? $conversation->avatar : $conversation->participants->first()?->profile?->avatar,
                    'is_group' => $conversation->isGroup(),
                    'participants_count' => $conversation->active_participants_count,
                    'is_admin' => $conversation->isAdmin($user),
                    'last_message' => $conversation->lastMessage ? [
                        'id' => $conversation->lastMessage->id,
                        'content' => $conversation->lastMessage->content,
                        'type' => $conversation->lastMessage->type,
                        'user_name' => $conversation->lastMessage->user->name,
                        'created_at' => $conversation->lastMessage->created_at,
                        'is_own' => $conversation->lastMessage->user_id === $user->id
                    ] : null,
                    'unread_count' => $conversation->getUnreadCountFor($user),
                    'last_message_at' => $conversation->last_message_at,
                    'created_at' => $conversation->created_at
                ];
            });

        return response()->json([
            'conversations' => $conversations
        ]);
    }

    /**
     * Créer une conversation privée
     */
    public function createPrivate(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id|different:' . $request->user()->id
        ]);

        $user = $request->user();
        $otherUser = User::findOrFail($request->user_id);

        // Vérifier si une conversation privée existe déjà
        $existingConversation = Conversation::where('type', 'private')
            ->whereHas('participants', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereHas('participants', function ($query) use ($otherUser) {
                $query->where('user_id', $otherUser->id);
            })
            ->first();

        if ($existingConversation) {
            return response()->json([
                'conversation' => [
                    'id' => $existingConversation->id,
                    'type' => 'private',
                    'name' => $otherUser->name,
                    'avatar' => $otherUser->profile?->avatar,
                    'is_group' => false
                ]
            ]);
        }

        // Créer une nouvelle conversation privée
        DB::beginTransaction();
        try {
            $conversation = Conversation::create([
                'type' => 'private',
                'created_by' => $user->id,
                'last_message_at' => now()
            ]);

            // Ajouter les participants
            $conversation->participants()->attach([
                $user->id => ['joined_at' => now()],
                $otherUser->id => ['joined_at' => now()]
            ]);

            DB::commit();

            return response()->json([
                'conversation' => [
                    'id' => $conversation->id,
                    'type' => 'private',
                    'name' => $otherUser->name,
                    'avatar' => $otherUser->profile?->avatar,
                    'is_group' => false
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Erreur lors de la création de la conversation'], 500);
        }
    }

    /**
     * Créer un groupe (membres de famille uniquement)
     */
    public function createGroup(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'participant_ids' => 'required|array|min:1|max:255',
            'participant_ids.*' => 'exists:users,id|different:' . $request->user()->id
        ]);

        $user = $request->user();

        // Vérifier que tous les participants sont des membres de la famille
        $familyMemberIds = $this->getFamilyMemberIds($user);
        $invalidParticipants = array_diff($request->participant_ids, $familyMemberIds);

        if (!empty($invalidParticipants)) {
            return response()->json([
                'error' => 'Seuls les membres de votre famille peuvent être ajoutés aux groupes.',
                'invalid_participants' => $invalidParticipants
            ], 422);
        }

        DB::beginTransaction();
        try {
            $conversation = Conversation::create([
                'name' => $request->name,
                'description' => $request->description,
                'type' => 'group',
                'created_by' => $user->id,
                'max_participants' => 256,
                'last_message_at' => now()
            ]);

            // Ajouter le créateur comme admin
            $conversation->participants()->attach($user->id, [
                'joined_at' => now(),
                'is_admin' => true,
                'role' => 'owner'
            ]);

            // Ajouter les autres participants
            $participantData = [];
            foreach ($request->participant_ids as $participantId) {
                $participantData[$participantId] = [
                    'joined_at' => now(),
                    'is_admin' => false,
                    'role' => 'member'
                ];
            }
            $conversation->participants()->attach($participantData);

            // Log de l'activité
            ConversationActivity::logGroupCreated($conversation, $user);

            // Message de bienvenue
            $welcomeMessage = Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'content' => "🎉 Groupe \"{$request->name}\" créé ! Bienvenue à tous !",
                'type' => 'text'
            ]);

            DB::commit();

            // Broadcast aux participants
            broadcast(new MessageSent($welcomeMessage, $user));

            return response()->json([
                'conversation' => [
                    'id' => $conversation->id,
                    'type' => 'group',
                    'name' => $conversation->name,
                    'description' => $conversation->description,
                    'avatar' => $conversation->avatar,
                    'is_group' => true,
                    'participants_count' => $conversation->active_participants_count,
                    'is_admin' => true
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Erreur lors de la création du groupe'], 500);
        }
    }

    /**
     * Obtenir les messages d'une conversation avec pagination
     */
    public function getMessages(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        // Vérifier que l'utilisateur fait partie de la conversation
        if (!$conversation->hasParticipant($user)) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        $messages = $conversation->messages()
            ->with(['user.profile', 'replyTo.user'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        $formattedMessages = $messages->getCollection()->map(function ($message) use ($user) {
            return [
                'id' => $message->id,
                'content' => $message->content,
                'type' => $message->type,
                'file_url' => $message->file_url,
                'file_name' => $message->file_name,
                'file_size' => $message->file_size,
                'is_own' => $message->user_id === $user->id,
                'user' => [
                    'id' => $message->user->id,
                    'name' => $message->user->name,
                    'avatar' => $message->user->profile?->avatar
                ],
                'reply_to' => $message->replyTo ? [
                    'id' => $message->replyTo->id,
                    'content' => $message->replyTo->content,
                    'user_name' => $message->replyTo->user->name
                ] : null,
                'is_edited' => $message->is_edited,
                'edited_at' => $message->edited_at,
                'created_at' => $message->created_at
            ];
        });

        return response()->json([
            'messages' => $formattedMessages,
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
                'has_more' => $messages->hasMorePages()
            ]
        ]);
    }

    /**
     * Envoyer un message
     */
    public function sendMessage(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        // Vérifier que l'utilisateur fait partie de la conversation
        if (!$conversation->hasParticipant($user)) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        $request->validate([
            'content' => 'required_without:file|string|max:4000',
            'file' => 'nullable|file|max:10240', // 10MB max
            'reply_to_id' => 'nullable|exists:messages,id'
        ]);

        DB::beginTransaction();
        try {
            $messageData = [
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'content' => $request->content ?? '',
                'type' => 'text',
                'reply_to_id' => $request->reply_to_id
            ];

            // Gérer l'upload de fichier
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $path = $file->store('messages', 'public');

                $messageData['file_url'] = Storage::url($path);
                $messageData['file_name'] = $file->getClientOriginalName();
                $messageData['file_size'] = $file->getSize();
                $messageData['type'] = $this->getFileType($file);

                if (empty($messageData['content'])) {
                    $messageData['content'] = $file->getClientOriginalName();
                }
            }

            $message = Message::create($messageData);
            $conversation->update(['last_message_at' => now()]);

            // Charger les relations pour la réponse
            $message->load(['user.profile', 'replyTo.user']);

            DB::commit();

            // Broadcast en temps réel
            broadcast(new MessageSent($message, $user));

            return response()->json([
                'message' => [
                    'id' => $message->id,
                    'content' => $message->content,
                    'type' => $message->type,
                    'file_url' => $message->file_url,
                    'file_name' => $message->file_name,
                    'file_size' => $message->file_size,
                    'user' => [
                        'id' => $message->user->id,
                        'name' => $message->user->name,
                        'avatar' => $message->user->profile?->avatar
                    ],
                    'reply_to' => $message->replyTo ? [
                        'id' => $message->replyTo->id,
                        'content' => $message->replyTo->content,
                        'user_name' => $message->replyTo->user->name
                    ] : null,
                    'created_at' => $message->created_at
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Erreur lors de l\'envoi du message'], 500);
        }
    }

    /**
     * Déterminer le type de fichier
     */
    private function getFileType($file): string
    {
        $mimeType = $file->getMimeType();

        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        } elseif (str_starts_with($mimeType, 'video/')) {
            return 'video';
        } elseif (str_starts_with($mimeType, 'audio/')) {
            return 'audio';
        }

        return 'file';
    }

    /**
     * Récupérer les IDs des membres de la famille d'un utilisateur
     */
    private function getFamilyMemberIds(User $user): array
    {
        // Utiliser la méthode existante du modèle User
        $familyMembers = $user->getRelatedUsers();
        return $familyMembers->pluck('id')->toArray();
    }

    /**
     * Obtenir la liste des membres de famille pour les groupes
     */
    public function getFamilyMembers(Request $request): JsonResponse
    {
        $user = $request->user();
        $familyMemberIds = $this->getFamilyMemberIds($user);

        if (empty($familyMemberIds)) {
            return response()->json([
                'family_members' => [],
                'message' => 'Aucun membre de famille trouvé. Ajoutez des relations familiales pour créer des groupes.'
            ]);
        }

        $familyMembers = $user->getRelatedUsers()
            ->load('profile')
            ->map(function ($member) {
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'avatar' => $member->profile?->avatar,
                ];
            });

        return response()->json([
            'family_members' => $familyMembers
        ]);
    }
}
