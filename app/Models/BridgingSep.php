<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BridgingSep extends Model
{
    use HasFactory;

    protected $table = 'bridging_sep';
    protected $primaryKey = 'no_sep';
    public $incrementing = false;
    public $timestamps = false;

    public function RegPeriksa()
    {
        return $this->hasMany(RegPeriksa::class, 'no_rawat', 'no_rawat');
    }
}
