<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ArtisanProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'workshop_name',
        'story',
        'city',
        'barangay',
        'profile_image',
        'id_photo',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'artisan_id', 'user_id');
    }

    // Accessors
    public function getProfileImageUrlAttribute(): ?string
    {
        if (! $this->profile_image) {
            return null;
        }

        return Storage::disk('artisans')->url(ltrim($this->profile_image, '/'));
    }

    public function getIdPhotoUrlAttribute(): ?string
    {
        if (! $this->id_photo) {
            return null;
        }

        return Storage::disk('public')->url(ltrim($this->id_photo, '/'));
    }

    public function getFullLocationAttribute(): string
    {
        $parts = array_filter([$this->barangay, $this->city]);
        return implode(', ', $parts);
    }

    // Mutators
    public function setProfileImageAttribute($value)
    {
        if ($this->profile_image && $this->profile_image !== $value) {
            Storage::disk('artisans')->delete($this->profile_image);
        }

        $this->attributes['profile_image'] = $value;
    }

    public function setIdPhotoAttribute($value)
    {
        if ($this->id_photo && $this->id_photo !== $value) {
            Storage::disk('public')->delete($this->id_photo);
        }

        $this->attributes['id_photo'] = $value;
    }
}