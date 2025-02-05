<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPeriksaLab extends Model
{
    use HasFactory;

    protected $table = 'detail_periksa_lab';
    protected $primaryKey = 'kd_jenis_prw';
    public $incrementing = false;
    public $timestamps = false;

    public function kode()
    {
        return $this->belongsTo(JnsPerawatanLab::class, 'kd_jenis_prw', 'kd_jenis_prw');
    }

}
