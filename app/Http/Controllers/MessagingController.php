<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\FamilyRelationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use App\Events\MessageSent;

class MessagingController extends Controller
{
    protected FamilyRelationService $familyRelationService;

    public function __construct(FamilyRelationService $familyRelationService)
    {
        $this->familyRelationService = $familyRelationService;
    }
    /**
     * Afficher l'interface de messagerie
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $selectedContactId = $request->query('selectedContactId');

        Log::info('Messagerie - Début', [
            'user_id' => $user->id,
            'selectedContactId' => $selectedContactId,
            'url' => $request->fullUrl()
        ]);

        // Mettre à jour le last_seen_at de l'utilisateur
        $user->update(['last_seen_at' => now()]);

        // Récupérer les conversations de l'utilisateur
        $conversations = $user->conversations()
            ->with([
                'lastMessage.user',
                'participants' => function ($query) use ($user) {
                    $query->where('user_id', '!=', $user->id);
                }
            ])
            ->orderBy('last_message_at', 'desc')
            ->get()
            ->map(function ($conversation) use ($user) {
                $otherParticipants = $conversation->participants->where('id', '!=', $user->id);

                return [
                    'id' => $conversation->id,
                    'name' => $conversation->display_name,
                    'type' => $conversation->type,
                    'avatar' => $conversation->avatar,
                    'last_message' => $conversation->lastMessage ? [
                        'content' => $conversation->lastMessage->content,
                        'created_at' => $conversation->lastMessage->created_at,
                        'user_name' => $conversation->lastMessage->user->name,
                        'is_own' => $conversation->lastMessage->user_id === $user->id
                    ] : null,
                    'unread_count' => $conversation->getUnreadCountFor($user),
                    'is_online' => $conversation->type === 'private' ?
                        $otherParticipants->first()?->isOnline() ?? false : false,
                    'participants_count' => $otherParticipants->count(),
                    'other_participant_id' => $conversation->type === 'private' ?
                        $otherParticipants->first()?->id : null
                ];
            });

        // Si un contact est sélectionné, essayer de trouver ou créer la conversation
        $selectedConversation = null;
        $targetUser = null;

        if ($selectedContactId) {
            $targetUser = User::find($selectedContactId);

            if ($targetUser) {
                // Chercher une conversation existante entre les deux utilisateurs
                $existingConversation = $conversations->first(function ($conv) use ($selectedContactId) {
                    return $conv['type'] === 'private' && $conv['other_participant_id'] == $selectedContactId;
                });

                if ($existingConversation) {
                    $selectedConversation = $existingConversation;
                } else {
                    // Créer automatiquement une nouvelle conversation
                    try {
                        DB::beginTransaction();

                        $conversation = Conversation::create([
                            'name' => null,
                            'type' => 'private',
                            'created_by' => $user->id,
                            'last_message_at' => now()
                        ]);

                        // Ajouter les participants
                        $conversation->addParticipant($user, true);
                        $conversation->addParticipant($targetUser);

                        DB::commit();

                        // Préparer les données de la conversation pour l'interface
                        $selectedConversation = [
                            'id' => $conversation->id,
                            'name' => $targetUser->name,
                            'type' => 'private',
                            'avatar' => $targetUser->profile?->avatar_url,
                            'last_message' => null,
                            'unread_count' => 0,
                            'is_online' => $targetUser->isOnline(),
                            'participants_count' => 1,
                            'other_participant_id' => $targetUser->id,
                            'is_new' => true
                        ];

                        // Ajouter la nouvelle conversation à la liste
                        $conversations->prepend($selectedConversation);

                    } catch (\Exception $e) {
                        DB::rollback();
                        // En cas d'erreur, créer une conversation virtuelle
                        $selectedConversation = [
                            'id' => null,
                            'name' => $targetUser->name,
                            'type' => 'private',
                            'avatar' => $targetUser->profile?->avatar_url,
                            'last_message' => null,
                            'unread_count' => 0,
                            'is_online' => $targetUser->isOnline(),
                            'participants_count' => 1,
                            'other_participant_id' => $targetUser->id,
                            'is_new' => true
                        ];
                    }
                }
            }
        }

        // Charger les messages de la conversation sélectionnée
        $messages = [];
        if ($selectedConversation && isset($selectedConversation['id']) && $selectedConversation['id']) {
            $conversation = Conversation::find($selectedConversation['id']);
            if ($conversation && $conversation->hasParticipant($user)) {
                $messages = $conversation->messages()
                    ->with(['user.profile'])
                    ->orderBy('created_at', 'asc')
                    ->get()
                    ->map(function ($message) {
                        return [
                            'id' => $message->id,
                            'content' => $message->content,
                            'type' => $message->type,
                            'file_url' => $message->file_url,
                            'file_name' => $message->file_name,
                            'created_at' => $message->created_at->toISOString(),
                            'user' => [
                                'id' => $message->user->id,
                                'name' => $message->user->name,
                                'avatar' => $message->user->profile?->avatar_url
                            ]
                        ];
                    })->toArray();

                Log::info('Messages chargés pour conversation ' . $selectedConversation['id'], [
                    'count' => count($messages),
                    'conversation_id' => $selectedConversation['id']
                ]);
            } else {
                Log::warning('Conversation non trouvée ou accès refusé', [
                    'conversation_id' => $selectedConversation['id'],
                    'user_id' => $user->id
                ]);
            }
        } else {
            Log::info('Aucune conversation sélectionnée', [
                'selectedContactId' => $selectedContactId,
                'selectedConversation' => $selectedConversation
            ]);
        }

        Log::info('Messagerie - Données finales', [
            'conversations_count' => $conversations->count(),
            'selectedConversation' => $selectedConversation ? [
                'id' => $selectedConversation['id'],
                'name' => $selectedConversation['name'],
                'type' => $selectedConversation['type']
            ] : null,
            'messages_count' => count($messages),
            'targetUser' => $targetUser ? $targetUser->name : null
        ]);

        return Inertia::render('Messaging/Index', [
            'conversations' => $conversations,
            'selectedConversation' => $selectedConversation,
            'messages' => $messages,
            'targetUser' => $targetUser ? [
                'id' => $targetUser->id,
                'name' => $targetUser->name,
                'avatar' => $targetUser->profile?->avatar_url
            ] : null,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->profile?->avatar
            ]
        ]);
    }

    /**
     * Récupérer les messages d'une conversation
     */
    public function getMessages(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        // Vérifier que l'utilisateur fait partie de la conversation
        if (!$conversation->hasParticipant($user)) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        $messages = $conversation->messages()
            ->with(['user', 'replyTo.user', 'reactions.user'])
            ->orderBy('created_at', 'asc')
            ->limit(50)
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'content' => $message->content,
                    'type' => $message->type,
                    'file_url' => $message->file_url,
                    'file_name' => $message->file_name,
                    'file_size' => $message->formatted_file_size,
                    'created_at' => $message->created_at,
                    'is_edited' => $message->is_edited,
                    'edited_at' => $message->edited_at,
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
                    'reactions' => $message->reactions->groupBy('emoji')->map(function ($reactions, $emoji) {
                        return [
                            'emoji' => $emoji,
                            'count' => $reactions->count(),
                            'users' => $reactions->map(fn($r) => $r->user->name)->toArray()
                        ];
                    })->values()
                ];
            });

        // Marquer la conversation comme lue
        $conversation->markAsReadFor($user);

        return response()->json([
            'messages' => $messages,
            'conversation' => [
                'id' => $conversation->id,
                'name' => $conversation->display_name,
                'type' => $conversation->type,
                'avatar' => $conversation->avatar,
                'participants' => $conversation->participants->map(function ($participant) {
                    return [
                        'id' => $participant->id,
                        'name' => $participant->name,
                        'avatar' => $participant->profile?->avatar,
                        'is_online' => $participant->isOnline()
                    ];
                })
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
            'content' => 'required_without:file|string|max:2000',
            'file' => 'nullable|file|max:10240', // 10MB max
            'reply_to_id' => 'nullable|exists:messages,id'
        ]);

        DB::beginTransaction();
        try {
            $messageData = [
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'content' => $request->content,
                'type' => 'text',
                'reply_to_id' => $request->reply_to_id
            ];

            // Gérer l'upload de fichier
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $path = $file->store('messages', 'public');

                $messageData['file_path'] = $path;
                $messageData['file_name'] = $file->getClientOriginalName();
                $messageData['file_size'] = $file->getSize();
                $messageData['type'] = $this->getFileType($file);
                $messageData['content'] = $messageData['content'] ?: $file->getClientOriginalName();
            }

            $message = Message::create($messageData);

            // Mettre à jour la conversation
            $conversation->update(['last_message_at' => now()]);

            DB::commit();

            // Charger les relations pour la réponse
            $message->load(['user', 'replyTo.user']);

            return response()->json([
                'message' => [
                    'id' => $message->id,
                    'content' => $message->content,
                    'type' => $message->type,
                    'file_url' => $message->file_url,
                    'file_name' => $message->file_name,
                    'file_size' => $message->formatted_file_size,
                    'created_at' => $message->created_at,
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
                    'reactions' => []
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Erreur lors de l\'envoi du message'], 500);
        }
    }

    /**
     * Marquer une conversation comme lue
     */
    public function markAsRead(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        // Vérifier que l'utilisateur fait partie de la conversation
        if (!$conversation->hasParticipant($user)) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        try {
            // Marquer tous les messages non lus comme lus
            $conversation->messages()
                ->whereDoesntHave('readBy', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->get()
                ->each(function ($message) use ($user) {
                    $message->markAsReadBy($user);
                });

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors du marquage comme lu'], 500);
        }
    }

    /**
     * Créer une nouvelle conversation simple (Inertia)
     */
    public function startConversation(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'target_user_id' => 'required|exists:users,id|different:' . $user->id,
            'message' => 'required|string|max:1000',
        ]);

        $targetUser = User::findOrFail($request->target_user_id);
        $messageContent = $request->message;

        try {
            DB::beginTransaction();

            // Vérifier si une conversation privée existe déjà
            $existingConversation = Conversation::where('type', 'private')
                ->whereHas('participants', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->whereHas('participants', function ($query) use ($targetUser) {
                    $query->where('user_id', $targetUser->id);
                })
                ->first();

            if ($existingConversation) {
                // Envoyer le message dans la conversation existante
                Message::create([
                    'conversation_id' => $existingConversation->id,
                    'user_id' => $user->id,
                    'content' => $messageContent,
                    'type' => 'text',
                ]);

                $existingConversation->update(['last_message_at' => now()]);
                DB::commit();

                return redirect()->route('messages')->with('success', 'Message envoyé !');
            }

            // Créer une nouvelle conversation
            $conversation = Conversation::create([
                'name' => null, // Les conversations privées n'ont pas de nom
                'type' => 'private',
                'created_by' => $user->id,
                'last_message_at' => now()
            ]);

            // Ajouter les participants
            $conversation->addParticipant($user, true);
            $conversation->addParticipant($targetUser);

            // Envoyer le premier message
            Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'content' => $messageContent,
                'type' => 'text',
            ]);

            DB::commit();

            return redirect()->route('messages')->with('success', 'Conversation créée et message envoyé !');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Erreur lors de la création de la conversation');
        }
    }

    /**
     * Envoyer un message simple (Inertia)
     */
    public function sendSimpleMessage(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'message' => 'required|string|max:1000',
        ]);

        $conversation = Conversation::findOrFail($request->conversation_id);

        // Vérifier que l'utilisateur fait partie de la conversation
        if (!$conversation->hasParticipant($user)) {
            return redirect()->back()->with('error', 'Accès non autorisé');
        }

        try {
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'content' => $request->message,
                'type' => 'text',
            ]);

            $conversation->update(['last_message_at' => now()]);

            // Déclencher l'événement pour le temps réel
            broadcast(new MessageSent($message, $user));

            // Rediriger vers la messagerie avec la conversation sélectionnée
            $otherParticipant = $conversation->participants()
                ->where('user_id', '!=', $user->id)
                ->first();

            if ($otherParticipant) {
                return redirect("/messagerie?selectedContactId={$otherParticipant->user_id}")
                    ->with('success', 'Message envoyé !');
            }

            return redirect()->route('messages')->with('success', 'Message envoyé !');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de l\'envoi du message');
        }
    }

    /**
     * Créer un groupe familial via web (Inertia)
     */
    public function createFamilyGroupWeb(Request $request)
    {
        $user = $request->user();
        $groupName = $request->query('name', "Famille {$user->name}");

        try {
            $conversation = $this->familyRelationService->createFamilyGroupConversation($user, $groupName);

            if (!$conversation) {
                return redirect()->route('messages')->with('error', 'Impossible de créer le groupe familial. Vous devez avoir au moins 2 membres de famille.');
            }

            return redirect()->route('messages')->with('success', 'Groupe familial créé avec succès !');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la création du groupe familial');
        }
    }

    /**
     * Rechercher des utilisateurs pour démarrer une conversation
     */
    public function searchUsers(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $user = $request->user();

        if (strlen($query) < 2) {
            // Si pas de recherche, retourner les suggestions de famille
            $familyMembers = $this->familyRelationService->getConversationSuggestions($user);
            return response()->json(['users' => $familyMembers->values()]);
        }

        // Recherche dans les membres de la famille d'abord
        $familyMembers = $this->familyRelationService->getFamilyMembersForMessaging($user)
            ->filter(function ($member) use ($query) {
                return stripos($member['name'], $query) !== false ||
                       stripos($member['email'], $query) !== false;
            });

        // Recherche dans tous les utilisateurs si pas assez de résultats familiaux
        $allUsers = collect();
        if ($familyMembers->count() < 5) {
            $allUsers = User::where('id', '!=', $user->id)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('email', 'like', "%{$query}%");
                })
                ->with('profile')
                ->limit(10 - $familyMembers->count())
                ->get()
                ->map(function ($foundUser) {
                    return [
                        'id' => $foundUser->id,
                        'name' => $foundUser->name,
                        'email' => $foundUser->email,
                        'avatar' => $foundUser->profile?->avatar,
                        'is_online' => $foundUser->isOnline(),
                        'relationship' => null,
                        'is_family' => false
                    ];
                })
                ->filter(function ($foundUser) use ($familyMembers) {
                    // Éviter les doublons avec les membres de famille
                    return !$familyMembers->contains('id', $foundUser['id']);
                });
        }

        // Marquer les membres de famille
        $familyMembersWithFlag = $familyMembers->map(function ($member) {
            $member['is_family'] = true;
            return $member;
        });

        // Combiner et trier (famille en premier)
        $results = $familyMembersWithFlag->merge($allUsers)
            ->sortBy([
                ['is_family', 'desc'], // Famille en premier
                ['name', 'asc']        // Puis par nom
            ])
            ->values();

        return response()->json(['users' => $results]);
    }

    /**
     * Créer un groupe familial automatiquement
     */
    public function createFamilyGroup(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'name' => 'nullable|string|max:100'
        ]);

        $groupName = $request->get('name');

        $conversation = $this->familyRelationService->createFamilyGroupConversation($user, $groupName);

        if (!$conversation) {
            return response()->json([
                'error' => 'Impossible de créer le groupe familial. Vous devez avoir au moins 2 membres de famille.'
            ], 400);
        }

        return response()->json([
            'conversation_id' => $conversation->id,
            'message' => 'Groupe familial créé avec succès !'
        ]);
    }

    /**
     * Obtenir les suggestions de conversations basées sur la famille
     */
    public function getFamilySuggestions(Request $request): JsonResponse
    {
        $user = $request->user();

        $suggestions = $this->familyRelationService->getConversationSuggestions($user);

        return response()->json([
            'suggestions' => $suggestions->values(),
            'can_create_family_group' => $suggestions->count() >= 2
        ]);
    }

    /**
     * Rechercher dans les messages
     */
    public function searchMessages(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'q' => 'required|string|min:2|max:100',
            'type' => 'nullable|in:text,image,file,audio,video',
            'date_range' => 'nullable|in:today,week,month',
            'user_id' => 'nullable|exists:users,id'
        ]);

        $query = $request->get('q');
        $type = $request->get('type');
        $dateRange = $request->get('date_range');
        $userId = $request->get('user_id');

        // Construire la requête de recherche
        $messagesQuery = Message::query()
            ->with(['user', 'conversation'])
            ->whereHas('conversation.participants', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->where(function ($q) use ($query) {
                $q->where('content', 'like', "%{$query}%")
                  ->orWhere('file_name', 'like', "%{$query}%");
            });

        // Filtrer par type
        if ($type) {
            $messagesQuery->where('type', $type);
        }

        // Filtrer par date
        if ($dateRange) {
            switch ($dateRange) {
                case 'today':
                    $messagesQuery->whereDate('created_at', today());
                    break;
                case 'week':
                    $messagesQuery->where('created_at', '>=', now()->subWeek());
                    break;
                case 'month':
                    $messagesQuery->where('created_at', '>=', now()->subMonth());
                    break;
            }
        }

        // Filtrer par utilisateur
        if ($userId) {
            $messagesQuery->where('user_id', $userId);
        }

        $messages = $messagesQuery
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'content' => $message->content,
                    'type' => $message->type,
                    'file_name' => $message->file_name,
                    'created_at' => $message->created_at,
                    'user' => [
                        'id' => $message->user->id,
                        'name' => $message->user->name,
                        'avatar' => $message->user->profile?->avatar
                    ],
                    'conversation' => [
                        'id' => $message->conversation->id,
                        'name' => $message->conversation->display_name
                    ]
                ];
            });

        return response()->json(['results' => $messages]);
    }

    /**
     * Obtenir les statistiques de messagerie
     */
    public function getStats(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $range = $request->get('range', 'month');

            // Définir la période
            $startDate = match($range) {
                'week' => now()->subWeek(),
                'month' => now()->subMonth(),
                'year' => now()->subYear(),
                default => now()->subMonth()
            };

            // Conversations de l'utilisateur
            $userConversations = $user->conversations()->pluck('conversations.id');

            // Si l'utilisateur n'a pas de conversations, retourner des stats vides
            if ($userConversations->isEmpty()) {
                return response()->json([
                    'totalMessages' => 0,
                    'totalConversations' => 0,
                    'unread_messages' => 0,
                    'unread_conversations' => 0,
                    'activeUsers' => 0,
                    'averageResponseTime' => 0,
                    'messagesThisWeek' => 0,
                    'messagesThisMonth' => 0,
                    'topContacts' => [],
                    'dailyActivity' => []
                ]);
            }

            // Messages non lus
            $unreadMessages = Message::whereIn('conversation_id', $userConversations)
                ->where('user_id', '!=', $user->id)
                ->whereNull('read_at')
                ->count();

            // Conversations avec messages non lus
            $unreadConversations = Conversation::whereIn('id', $userConversations)
                ->whereHas('messages', function ($query) use ($user) {
                    $query->where('user_id', '!=', $user->id)
                          ->whereNull('read_at');
                })
                ->count();

        // Statistiques générales
        $totalMessages = Message::whereIn('conversation_id', $userConversations)->count();
        $totalConversations = $userConversations->count();
        $activeUsers = User::whereHas('messages', function ($query) use ($userConversations, $startDate) {
            $query->whereIn('conversation_id', $userConversations)
                  ->where('created_at', '>=', $startDate);
        })->count();

        // Messages récents
        $messagesThisWeek = Message::whereIn('conversation_id', $userConversations)
            ->where('created_at', '>=', now()->subWeek())
            ->count();

        $messagesThisMonth = Message::whereIn('conversation_id', $userConversations)
            ->where('created_at', '>=', now()->subMonth())
            ->count();

        // Top contacts
        $topContacts = Message::whereIn('conversation_id', $userConversations)
            ->where('user_id', '!=', $user->id)
            ->where('created_at', '>=', $startDate)
            ->with('user.profile')
            ->get()
            ->groupBy('user_id')
            ->map(function ($messages) {
                $user = $messages->first()->user;
                return [
                    'name' => $user->name,
                    'messageCount' => $messages->count(),
                    'avatar' => $user->profile?->avatar
                ];
            })
            ->sortByDesc('messageCount')
            ->take(5)
            ->values();

        // Activité quotidienne (7 derniers jours)
        $dailyActivity = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = Message::whereIn('conversation_id', $userConversations)
                ->whereDate('created_at', $date)
                ->count();

            $dailyActivity->push([
                'date' => $date->format('d/m'),
                'count' => $count
            ]);
        }

        // Temps de réponse moyen (approximatif)
        $averageResponseTime = 45; // En minutes - à calculer plus précisément si nécessaire

        return response()->json([
            'totalMessages' => $totalMessages,
            'totalConversations' => $totalConversations,
            'unread_messages' => $unreadMessages,
            'unread_conversations' => $unreadConversations,
            'activeUsers' => $activeUsers,
            'averageResponseTime' => $averageResponseTime,
            'messagesThisWeek' => $messagesThisWeek,
            'messagesThisMonth' => $messagesThisMonth,
            'topContacts' => $topContacts,
            'dailyActivity' => $dailyActivity
        ]);

        } catch (\Exception $e) {
            Log::error('Error in getStats: ' . $e->getMessage(), [
                'user_id' => $request->user()?->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'totalMessages' => 0,
                'totalConversations' => 0,
                'unread_messages' => 0,
                'unread_conversations' => 0,
                'activeUsers' => 0,
                'averageResponseTime' => 0,
                'messagesThisWeek' => 0,
                'messagesThisMonth' => 0,
                'topContacts' => [],
                'dailyActivity' => []
            ], 200);
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
        } elseif (str_starts_with($mimeType, 'audio/')) {
            return 'audio';
        } elseif (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }

        return 'file';
    }
}
