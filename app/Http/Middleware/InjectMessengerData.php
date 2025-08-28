<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class InjectMessengerData
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Injecter les données Messenger seulement pour les utilisateurs authentifiés
        if (Auth::check() && $request->inertia()) {
            $user = Auth::user();

            try {
                // Récupérer les conversations avec requête optimisée
                $conversations = $this->getConversationsSummary($user);
                $totalUnreadCount = collect($conversations)->sum('unread_count');

                // Partager les données avec Inertia
                Inertia::share([
                    'messengerData' => [
                        'conversations' => $conversations,
                        'totalUnreadCount' => $totalUnreadCount,
                        'lastUpdated' => now()->timestamp
                    ]
                ]);
            } catch (\Exception $e) {
                // En cas d'erreur, partager des données vides
                Log::error('Erreur InjectMessengerData: ' . $e->getMessage());

                Inertia::share([
                    'messengerData' => [
                        'conversations' => [],
                        'totalUnreadCount' => 0,
                        'lastUpdated' => now()->timestamp
                    ]
                ]);
            }
        }

        return $next($request);
    }

    private function getConversationsSummary($user)
    {
        // Requête optimisée similaire à celle du contrôleur
        $conversations = DB::table('conversations as c')
            ->join('conversation_participants as cp', 'c.id', '=', 'cp.conversation_id')
            ->leftJoin('messages as m', function ($join) {
                $join->on('c.id', '=', 'm.conversation_id')
                     ->whereRaw('m.id = (SELECT MAX(id) FROM messages WHERE conversation_id = c.id)');
            })
            ->leftJoin('users as msg_user', 'm.user_id', '=', 'msg_user.id')
            ->where('cp.user_id', $user->id)
            ->where('cp.status', 'active')
            ->whereNull('cp.left_at')
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
            ->limit(10) // Limiter pour les performances
            ->get();

        $result = [];

        foreach ($conversations as $conv) {
            // Calculer le nombre de messages non lus
            $unreadCount = DB::table('messages')
                ->where('conversation_id', $conv->id)
                ->where('user_id', '!=', $user->id)
                ->whereNotExists(function ($query) use ($user) {
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

            $result[] = [
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
        }

        return $result;
    }
}
