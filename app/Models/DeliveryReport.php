<?php

namespace App\Models;

use App\Support\PublicMediaUrl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DeliveryReport extends Model
{
    use HasFactory;

    public const STATUS_OPEN = 'open';

    public const STATUS_REVIEWED = 'reviewed';

    public const STATUS_RESOLVED = 'resolved';

    protected $fillable = [
        'order_package_id',
        'user_id',
        'concern',
        'details',
        'proof_image',
        'status',
        'reviewed_by',
        'admin_notes',
    ];

    public function orderPackage()
    {
        return $this->belongsTo(OrderPackage::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getProofImageUrlAttribute(): ?string
    {
        if (! $this->proof_image) {
            return null;
        }

        return PublicMediaUrl::url('delivery_reports', $this->proof_image);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (DeliveryReport $report) {
            if ($report->proof_image) {
                Storage::disk('delivery_reports')->delete($report->proof_image);
            }
        });
    }
}
