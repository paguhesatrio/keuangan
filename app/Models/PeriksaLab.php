<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriksaLab extends Model
{
    use HasFactory;
    protected $table = 'periksa_lab';
    protected $primaryKey = 'no_rawat ';
    public $incrementing = false;
    public $timestamps = false;

    public function RegPeriksa()
    {
        return $this->belongsTo(RegPeriksa::class, 'no_rawat', 'no_rawat');
    }

    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'dokter_perujuk', 'kd_dokter');
    }
    
    public function kode()
    {
        return $this->belongsTo(JnsPerawatanLab::class, 'kd_jenis_prw', 'kd_jenis_prw');
    }
    
}
