<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Services\MessageService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MessageController extends Controller
{
    public function __construct(
        private MessageService $messageService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $messages = $this->messageService->getMessages($user);

        return Inertia::render('Messages', [
            'messages' => $messages,
        ]);
    }

    public function show(Request $request, int $conversationId): Response
    {
        $user = $request->user();
        $conversation = $this->messageService->getConversation($user, $conversationId);

        return Inertia::render('Messages/Conversation', [
            'conversation' => $conversation,
            'otherUserId' => $conversationId,
        ]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'content' => 'required|string|max:1000',
            'attachment' => 'nullable|string',
        ]);

        $user = $request->user();
        $this->messageService->sendMessage(
            $user,
            $validated['recipient_id'],
            $validated['content'],
            $validated['attachment'] ?? null
        );

        return back()->with('success', 'Message envoyé avec succès.');
    }

    public function destroy(Message $message): \Illuminate\Http\RedirectResponse
    {
        $message->delete();
        return back()->with('success', 'Message supprimé avec succès.');
    }
}
