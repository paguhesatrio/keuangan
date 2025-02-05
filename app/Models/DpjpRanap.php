<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DpjpRanap extends Model
{
    use HasFactory;

    protected $table = 'dpjp_ranap';
    protected $primaryKey = 'no_sep';
    public $incrementing = false;
    public $timestamps = false;

    public function RegPeriksa()
    {
        return $this->belongsTo(RegPeriksa::class, 'no_rawat', 'no_rawat');
    }

    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'kd_dokter', 'kd_dokter');
    }

}
