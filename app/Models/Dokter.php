<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dokter extends Model
{
    use HasFactory;

    protected $table = 'dokter';
    protected $primaryKey = 'kd_dokter';
    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string'; 

    public function ReqPeriksa()
    {
        return $this->hasMany(RegPeriksa::class, 'kd_dokter', 'kd_dokter');
    }

    public function dpjp()
    {
        return $this->belongsTo(DpjpRanap::class, 'kd_dokter', 'kd_dokter');
    }

    public function operasi()
    {
        return $this->belongsTo(DpjpRanap::class, 'kd_dokter', 'kd_dokter');
    }


    // public function pegawai()
    // {
    //     return $this->belongsTo(Pegawai::class, 'kd_dokter', 'nik');
    // }

    // public function spesialis()
    // {
    //     return $this->belongsTo(Spesialis::class, 'kd_sps', 'kd_sps');
    // }
    
}
