<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawatJl extends Model
{
    use HasFactory;

    protected $table = 'rawat_jl_dr';
    protected $primaryKey = 'no_rawat';
    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string'; 

    public function ReqPeriksa()
    {
        return $this->hasMany(RegPeriksa::class, 'no_rawat', 'no_rawat');
    }

}
