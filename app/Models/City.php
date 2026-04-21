<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = ['name', 'code', 'province_id'];

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function barangays()
    {
        return $this->hasMany(Barangay::class);
    }
}
