<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Party extends Model
{
    protected $table = 'party';
    protected $primaryKey = 'id';
    public $timestamps = false;

    /**
     * The abbreviation stored against results. The party table lists
     * "LABOUR" but the results column (char 4) holds "LABO".
     */
    public function getCodeAttribute(): string
    {
        return $this->partyid === 'LABOUR' ? 'LABO' : $this->partyid;
    }
}
