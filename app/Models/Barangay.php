<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barangay extends Model
{
    protected $fillable = ['name', 'code', 'city_id'];

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
