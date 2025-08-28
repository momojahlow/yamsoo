<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MessageReactionController extends Controller
{
    /**
     * Toggle a reaction on a message
     */
    public function toggle(Request $request, Message $message): JsonResponse
    {
        $request->validate([
            'emoji' => 'required|string|max:10'
        ]);

        $user = $request->user();
        $emoji = $request->emoji;

        // Check if user already reacted with this emoji
        $existingReaction = $message->reactions()
            ->where('user_id', $user->id)
            ->where('emoji', $emoji)
            ->first();

        if ($existingReaction) {
            // Remove reaction
            $existingReaction->delete();
            $action = 'removed';
        } else {
            // Add reaction
            $message->reactions()->create([
                'user_id' => $user->id,
                'emoji' => $emoji
            ]);
            $action = 'added';
        }

        return response()->json([
            'success' => true,
            'action' => $action,
            'emoji' => $emoji,
            'reactions' => $message->reactions()->with('user:id,name')->get()
        ]);
    }

    /**
     * Get all reactions for a message
     */
    public function index(Message $message): JsonResponse
    {
        $reactions = $message->reactions()
            ->with('user:id,name')
            ->get()
            ->groupBy('emoji')
            ->map(function ($reactions, $emoji) {
                return [
                    'emoji' => $emoji,
                    'count' => $reactions->count(),
                    'users' => $reactions->pluck('user')
                ];
            })
            ->values();

        return response()->json([
            'reactions' => $reactions
        ]);
    }
}
