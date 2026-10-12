<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lga extends Model
{
    protected $table = 'lga';
    protected $primaryKey = 'uniqueid';
    public $timestamps = false;

    public function wards()
    {
        return $this->hasMany(Ward::class, 'lga_id', 'lga_id');
    }
}
