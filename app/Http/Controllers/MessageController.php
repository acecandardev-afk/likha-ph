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

        return back()->with('success', 'Message sent.');
    }
}