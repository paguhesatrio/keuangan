<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawatInapDrpr extends Model
{
    use HasFactory;
    
    protected $table = 'rawat_inap_drpr';
    protected $primaryKey = 'no_rawat';
    public $incrementing = false;
    public $timestamps = false;

    public function RegPeriksa()
    {
        return $this->belongsTo(RegPeriksa::class, 'no_rawat', 'no_rawat');
    }

     public function JnsPerawatanInap()
    {
        return $this->belongsTo(JnsPerawatanInap::class, 'kd_jenis_prw', 'kd_jenis_prw');
    }

    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'kd_dokter', 'kd_dokter');
    }

}
