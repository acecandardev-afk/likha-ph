<?php

namespace App\Http\Controllers;

use App\Models\DirectMessage;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class DirectMessageController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {
        $this->middleware('auth');
    }

    /**
     * List conversations for the current user.
     */
    public function conversations()
    {
        $userId = auth()->id();
        $messages = DirectMessage::where('sender_id', $userId)
            ->orWhere('recipient_id', $userId)
            ->with(['sender.artisanProfile', 'recipient.artisanProfile'])
            ->orderByDesc('created_at')
            ->get();

        $byPartner = [];
        foreach ($messages as $m) {
            $otherId = $m->sender_id == $userId ? $m->recipient_id : $m->sender_id;
            if (!isset($byPartner[$otherId])) {
                $other = $m->sender_id == $userId ? $m->recipient : $m->sender;
                $byPartner[$otherId] = [
                    'user' => $other,
                    'name' => $other->artisanProfile->workshop_name ?? $other->name ?? 'User',
                    'last_message' => $m->message,
                    'last_at' => $m->created_at,
                ];
            }
        }

        $conversations = collect($byPartner)->sortByDesc('last_at')->values();

        return view('chats.index', compact('conversations'));
    }

    /**
     * Show chat with a user (page) or get messages (JSON for AJAX).
     */
    public function index(User $user)
    {
        if ($user->id === auth()->id()) {
            return request()->wantsJson()
                ? response()->json(['error' => 'You cannot chat with yourself.'], 403)
                : redirect()->route('chats.index');
        }

        $messages = DirectMessage::between(auth()->id(), $user->id)
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'message' => $m->message,
                'sender_id' => $m->sender_id,
                'sender_name' => $m->sender->name,
                'is_mine' => $m->sender_id === auth()->id(),
                'created_at' => $m->created_at->format('M j, Y g:i A'),
            ]);

        if (request()->wantsJson()) {
            return response()->json(['messages' => $messages]);
        }

        $otherName = $user->artisanProfile->workshop_name ?? $user->name;
        return view('chats.show', compact('user', 'otherName'));
    }

    /**
     * Send a message (JSON for AJAX).
     */
    public function store(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json(['error' => 'You cannot chat with yourself.'], 403);
        }

        $validated = $request->validate([
            'message' => 'required|string|min:1|max:1000',
        ]);

        $msg = DirectMessage::create([
            'sender_id' => auth()->id(),
            'recipient_id' => $user->id,
            'message' => trim($validated['message']),
        ]);

        $msg->load('sender:id,name');

        $this->notificationService->notifyDirectMessageReceived(
            $user,
            $request->user(),
            $msg
        );

        return response()->json([
            'message' => [
                'id' => $msg->id,
                'message' => $msg->message,
                'sender_id' => $msg->sender_id,
                'sender_name' => $msg->sender->name,
                'is_mine' => true,
                'created_at' => $msg->created_at->format('M j, Y g:i A'),
            ],
        ], 201);
    }
}
