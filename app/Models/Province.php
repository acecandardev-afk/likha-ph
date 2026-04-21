<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $fillable = ['name', 'code', 'region_id'];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function cities()
    {
        return $this->hasMany(City::class);
    }
}
