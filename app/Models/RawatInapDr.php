<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawatInapDr extends Model
{
    use HasFactory;

    protected $table = 'rawat_inap_dr';
    protected $primaryKey = 'no_rawat';
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

    public function kode()
    {
        return $this->belongsTo(JnsPerawatanInap::class, 'kd_jenis_prw', 'kd_jenis_prw');
    }

    
}
