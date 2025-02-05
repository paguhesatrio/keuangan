<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JnsPerawatanInap extends Model
{
    use HasFactory;
    
    protected $table = 'jns_perawatan_inap';
    protected $primaryKey = 'kd_jenis_prw';
    public $incrementing = false;
    public $timestamps = false;

    public function rawatainapdr()
    {
        return $this->belongsTo(RawatInapDr::class, 'kd_jenis_prw ', 'kd_jenis_prw');
    }

    public function rawatainapdrpr()
    {
        return $this->belongsTo(RawatInapDrpr::class, 'kd_jenis_prw ', 'kd_jenis_prw');
    }
}
