<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_method',
        'amount',
        'proof_image',
        'verification_status',
        'verified_by',
        'verification_notes',
        'verified_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Scopes
    public function scopeAwaitingProof($query)
    {
        return $query->where('verification_status', 'awaiting_proof');
    }

    public function scopePending($query)
    {
        return $query->where('verification_status', 'pending');
    }

    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'verified');
    }

    // Accessors
    public function getProofImageUrlAttribute(): ?string
    {
        if (!$this->proof_image) {
            return null;
        }

        return '/storage/payments/'.ltrim($this->proof_image, '/');
    }

    // Helpers
    public function isAwaitingProof(): bool
    {
        return $this->verification_status === 'awaiting_proof';
    }

    public function isPending(): bool
    {
        return $this->verification_status === 'pending';
    }

    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    public function isRejected(): bool
    {
        return $this->verification_status === 'rejected';
    }

    // Mutators
    public function setProofImageAttribute($value)
    {
        if ($this->proof_image && $this->proof_image !== $value) {
            Storage::disk('payments')->delete($this->proof_image);
        }

        $this->attributes['proof_image'] = $value;
    }

    // Auto-delete proof on deletion
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($payment) {
            if ($payment->proof_image) {
                Storage::disk('payments')->delete($payment->proof_image);
            }
        });
    }
}