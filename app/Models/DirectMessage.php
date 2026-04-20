<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DirectMessage extends Model
{
    protected $fillable = [
        'sender_id',
        'recipient_id',
        'message',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * Get messages between two users (either direction).
     */
    public static function between(int $userId, int $otherUserId)
    {
        return static::query()
            ->where(function ($q) use ($userId, $otherUserId) {
                $q->where('sender_id', $userId)->where('recipient_id', $otherUserId);
            })
            ->orWhere(function ($q) use ($userId, $otherUserId) {
                $q->where('sender_id', $otherUserId)->where('recipient_id', $userId);
            })
            ->with('sender:id,name')
            ->orderBy('created_at');
    }
}
