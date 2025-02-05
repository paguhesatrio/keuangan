<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hemodialisa extends Model
{
    use HasFactory;

    protected $table = 'hemodialisa';
    protected $primaryKey = 'no_rawat';
    public $incrementing = false;
    public $timestamps = false;

    public function RegPeriksa()
    {
        return $this->belongsTo(RegPeriksa::class, 'no_rawat', 'no_rawat');
    }
}
