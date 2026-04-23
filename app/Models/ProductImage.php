<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Support\PublicMediaUrl;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'image_path',
        'is_primary',
        'sort_order',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Accessors
    public function getImageUrlAttribute(): string
    {
        return PublicMediaUrl::url('products', $this->image_path);
    }

    // Mutators
    public function setImagePathAttribute($value)
    {
        if ($this->image_path && $this->image_path !== $value) {
            Storage::disk('products')->delete($this->image_path);
        }

        $this->attributes['image_path'] = $value;
    }

    // Auto-delete image on model deletion
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($image) {
            Storage::disk('products')->delete($image->image_path);
        });
    }
}