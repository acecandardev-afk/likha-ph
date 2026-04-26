<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Order;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {
        $this->middleware('auth');
    }

    /**
     * Display messages for an order.
     */
    public function index(Order $order)
    {
        $this->authorize('viewMessages', $order);

        $messages = $order->messages()
            ->with('sender')
            ->orderBy('created_at')
            ->get();

        // Mark messages as read
        $order->messages()
            ->where('sender_id', '!=', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return view('messages.index', compact('order', 'messages'));
    }

    /**
     * Send a message.
     */
    public function store(Request $request, Order $order)
    {
        $this->authorize('sendMessage', $order);

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $message = Message::create([
            'order_id' => $order->id,
            'sender_id' => auth()->id(),
            'message' => $validated['message'],
            'is_read' => false,
        ]);

        $message->load('sender');
        $this->notificationService->notifyOrderThreadMessage($order, $message);

        // Return JSON if AJAX request
        if ($request->wantsJson()) {
            return response()->json([
                'message' => [
                    'id' => $message->id,
                    'sender_name' => $message->sender->name,
                    'message' => $message->message,
                    'created_at' => $message->created_at->format('M d, Y H:i'),
                    'is_own' => true,
                ],
                'success' => true,
            ]);
        }

        return back()->with('success', 'Message sent.');
    }

    /**
     * Fetch messages for an order (for real-time updates).
     */
    public function fetch(Order $order, Request $request)
    {
        $this->authorize('viewMessages', $order);

        $query = $order->messages()->with('sender');

        // Incremental polling by message id (preferred) or timestamp fallback.
        if ($request->filled('since_id')) {
            $query->where('id', '>', (int) $request->input('since_id'));
        } elseif ($request->has('since')) {
            $query->where('created_at', '>', $request->input('since'));
        }

        $messages = $query->orderBy('created_at')->get();

        // Mark new incoming messages as read only when there are unread foreign messages in this batch.
        $foreignMessageIds = $messages
            ->where('sender_id', '!=', auth()->id())
            ->pluck('id');
        if ($foreignMessageIds->isNotEmpty()) {
            $order->messages()
                ->whereIn('id', $foreignMessageIds)
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        return response()->json([
            'messages' => $messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'sender_name' => $message->sender->name,
                    'message' => $message->message,
                    'created_at' => $message->created_at->format('M d, Y H:i'),
                    'is_own' => $message->sender_id === auth()->id(),
                ];
            }),
        ]);
    }
}