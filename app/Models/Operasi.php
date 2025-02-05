<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operasi extends Model
{
    use HasFactory;

    protected $table = 'operasi';
    protected $primaryKey = 'no_rawat';
    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string';

    public function ReqPeriksa()
    {
        return $this->hasMany(RegPeriksa::class, 'no_rawat', 'no_rawat');
    }

    public function dokter1()
    {
        return $this->belongsTo(Dokter::class, 'operator1', 'kd_dokter');
    }

    public function dokter2()
    {
        return $this->belongsTo(Dokter::class, 'operator2', 'kd_dokter');
    }

    public function dokter3()
    {
        return $this->belongsTo(Dokter::class, 'operator3', 'kd_dokter');
    }

    public function anestesi()
    {
        return $this->belongsTo(Dokter::class, 'dokter_anestesi', 'kd_dokter');
    }

    public function paket()
    {
        return $this->belongsTo(PaketOperasi::class, 'kode_paket', 'kode_paket');
    }
}
